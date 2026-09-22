<?php

namespace App\Http\Controllers;

use App\Models\ExternalApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExternalApiKeyController extends Controller
{
    public function index()
    {
        $keys = ExternalApiKey::latest()
            ->get()
            ->map(function (ExternalApiKey $key) {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'key_prefix' => $key->key_prefix,
                    'is_active' => $key->is_active,
                    'last_used_at' => $key->last_used_at,
                    'created_at' => $key->created_at,
                ];
            });

        return response()->json([
            'keys' => $keys,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $plainKey = 'rag_live_'.Str::random(48);

        $keyPrefix = substr($plainKey, 0, 16);

        $apiKey = ExternalApiKey::create([
            'name' => $validated['name'] ?? 'External Chat API',
            'key_prefix' => $keyPrefix,
            'key_hash' => hash('sha256', $plainKey),
            'is_active' => true,
            'last_used_at' => null,
        ]);

        return response()->json([
            'message' => 'API key generated successfully.',
            'api_key' => $plainKey,
            'key' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key_prefix' => $apiKey->key_prefix,
                'is_active' => $apiKey->is_active,
                'created_at' => $apiKey->created_at,
            ],
        ], 201);
    }

    public function destroy(ExternalApiKey $externalApiKey)
    {
        $externalApiKey->delete();

        return response()->json([
            'message' => 'API key deleted successfully.',
        ]);
    }

    public function toggle(ExternalApiKey $externalApiKey)
    {
        $externalApiKey->update([
            'is_active' => !$externalApiKey->is_active,
        ]);

        return response()->json([
            'message' => $externalApiKey->is_active
                ? 'API key enabled successfully.'
                : 'API key disabled successfully.',
            'key' => [
                'id' => $externalApiKey->id,
                'name' => $externalApiKey->name,
                'key_prefix' => $externalApiKey->key_prefix,
                'is_active' => $externalApiKey->is_active,
            ],
        ]);
    }
}