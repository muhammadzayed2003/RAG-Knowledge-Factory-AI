<?php

namespace App\Http\Controllers;

use App\Models\ExternalApiKey;
use App\Services\RagChatService;
use Illuminate\Http\Request;

class ExternalChatController extends Controller
{
    public function __construct(
        protected RagChatService $ragChatService
    ) {
    }

    public function ask(Request $request)
    {
        $plainApiKey = $request->header('X-API-Key');

        if (!$plainApiKey) {
            return response()->json([
                'message' => 'API key is required.',
            ], 401);
        }

        $keyHash = hash(
            'sha256',
            $plainApiKey
        );

        $apiKey = ExternalApiKey::where(
            'key_hash',
            $keyHash
        )->first();

        if (!$apiKey) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], 401);
        }

        if (!$apiKey->is_active) {
            return response()->json([
                'message' => 'This API key is disabled.',
            ], 403);
        }

        $validated = $request->validate([
            'question' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        try {
            $result = $this->ragChatService->ask(
                $validated['question']
            );

            $apiKey->update([
                'last_used_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'answer' => $result['answer'],
                'sources' => $result['sources'],
            ]);

        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to generate answer.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
}