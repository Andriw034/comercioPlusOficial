<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        $store = $this->resolveMerchantStore($request);
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $baseQuery = CreditAccount::query()->where('store_id', $store->id);
        $accounts = (clone $baseQuery)
            ->with(['customer.user:id,name,email,phone'])
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json([
            'status' => 'ok',
            'data' => $accounts,
            'stats' => [
                'total_accounts' => (clone $baseQuery)->count(),
                'total_balance' => (float) ((clone $baseQuery)->sum('balance') ?? 0),
                'total_overdue' => (clone $baseQuery)->whereColumn('balance', '>', 'credit_limit')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $store = $this->resolveMerchantStore($request);
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'credit_limit' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            Customer::query()
                ->where('id', (int) $validated['customer_id'])
                ->where('store_id', (int) $store->id)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cliente no encontrado para esta tienda'], 404);
        }

        $account = CreditAccount::query()->firstOrCreate(
            [
                'store_id' => (int) $store->id,
                'customer_id' => (int) $validated['customer_id'],
            ],
            [
                'balance' => 0,
                'credit_limit' => (float) $validated['credit_limit'],
                'status' => 'active',
            ],
        );

        $account->update([
            'credit_limit' => (float) $validated['credit_limit'],
        ]);

        $account->load(['customer.user:id,name,email,phone']);

        return response()->json([
            'status' => 'ok',
            'data' => $account,
        ], 201);
    }

    public function show(Request $request, CreditAccount $creditAccount)
    {
        $store = $this->resolveMerchantStore($request);
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        if ((int) $creditAccount->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $creditAccount->load(['customer.user:id,name,email,phone']);
        $transactions = $creditAccount->transactions()->limit(50)->get();

        return response()->json([
            'status' => 'ok',
            'data' => $creditAccount,
            'transactions' => $transactions,
        ]);
    }

    public function charge(Request $request, CreditAccount $creditAccount)
    {
        $store = $this->resolveMerchantStore($request);
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        if ((int) $creditAccount->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
        ]);

        $result = DB::transaction(function () use ($creditAccount, $request, $validated) {
            $account = CreditAccount::query()->lockForUpdate()->findOrFail($creditAccount->id);
            $amount = (float) $validated['amount'];
            $newBalance = round(((float) $account->balance) + $amount, 2);

            $account->update([
                'balance' => $newBalance,
            ]);

            $transaction = CreditTransaction::query()->create([
                'credit_account_id' => (int) $account->id,
                'type' => 'charge',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'note' => $validated['note'] ?? null,
                'created_by' => (int) $request->user()->id,
            ]);

            return [
                'transaction' => $transaction,
                'balance' => $newBalance,
            ];
        });

        return response()->json([
            'status' => 'ok',
            'data' => $result['transaction'],
            'balance' => $result['balance'],
        ], 201);
    }

    public function payment(Request $request, CreditAccount $creditAccount)
    {
        $store = $this->resolveMerchantStore($request);
        if (! $store) {
            return response()->json(['message' => 'Tienda no encontrada'], 404);
        }

        if ((int) $creditAccount->store_id !== (int) $store->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $result = DB::transaction(function () use ($creditAccount, $request, $validated) {
                $account = CreditAccount::query()->lockForUpdate()->findOrFail($creditAccount->id);
                $amount = (float) $validated['amount'];

                if ((float) $account->balance < $amount) {
                    throw new HttpResponseException(response()->json([
                        'message' => 'El pago supera la deuda actual',
                    ], 422));
                }

                $newBalance = round(((float) $account->balance) - $amount, 2);

                $account->update([
                    'balance' => $newBalance,
                ]);

                $transaction = CreditTransaction::query()->create([
                    'credit_account_id' => (int) $account->id,
                    'type' => 'payment',
                    'amount' => $amount,
                    'balance_after' => $newBalance,
                    'note' => $validated['note'] ?? null,
                    'created_by' => (int) $request->user()->id,
                ]);

                return [
                    'transaction' => $transaction,
                    'balance' => $newBalance,
                ];
            });
        } catch (HttpResponseException $exception) {
            return $exception->getResponse();
        }

        return response()->json([
            'status' => 'ok',
            'data' => $result['transaction'],
            'balance' => $result['balance'],
        ], 201);
    }

    private function resolveMerchantStore(Request $request): ?Store
    {
        try {
            return Store::query()->where('user_id', $request->user()->id)->firstOrFail();
        } catch (ModelNotFoundException) {
            return null;
        }
    }
}
