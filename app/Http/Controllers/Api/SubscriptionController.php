<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserSubscription;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = UserSubscription::all();
        return response()->json($subscriptions);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'plan' => ['required', 'string'],
            'period' => ['required', 'in:monthly,yearly'],
        ]);

        $subscription = UserSubscription::create([
            'user_id' => $data['user_id'],
            'plan' => $data['plan'],
            'status' => 'active',
            'expires_at' => null,
        ]);
        return response()->json(['data' => $subscription], 201);
    }

    public function show($id)
    {
        $subscription = UserSubscription::findOrFail($id);
        return response()->json(['data' => $subscription]);
    }

    public function update(Request $request, $id)
    {
        $subscription = UserSubscription::findOrFail($id);

        $data = $request->validate([
            'plan' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string'],
        ]);

        $subscription->update($data);
        return response()->json(['data' => $subscription]);
    }

    public function destroy($id)
    {
        $subscription = UserSubscription::findOrFail($id);
        $subscription->delete();
        return response()->json([], 204);
    }
}
