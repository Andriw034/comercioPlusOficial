<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    private const KEY = 'merchant_settings';

    public function index(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $row = DB::table('settings')
            ->where('key', self::KEY)
            ->where('user_id', $userId)
            ->first();

        if (! $row) {
            return response()->json([]);
        }

        $decoded = json_decode((string) $row->value, true);

        return response()->json(is_array($decoded) ? $decoded : []);
    }

    public function update(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $payload = $request->only([
            'currency', 'timezone', 'language',
            'taxes', 'payments', 'shipping',
            'notifications', 'fiscal',
        ]);

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $exists = DB::table('settings')
            ->where('key', self::KEY)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            DB::table('settings')
                ->where('key', self::KEY)
                ->where('user_id', $userId)
                ->update(['value' => $encoded, 'updated_at' => now()]);
        } else {
            DB::table('settings')->insert([
                'key'        => self::KEY,
                'value'      => $encoded,
                'user_id'    => $userId,
                'role'       => 'merchant',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Configuración guardada']);
    }
}
