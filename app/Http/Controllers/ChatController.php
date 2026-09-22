<?php

namespace App\Http\Controllers;

use App\Services\RagChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected RagChatService $ragChatService
    ) {
    }

    public function ask(Request $request)
    {
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

            return response()->json([
                'message' => 'Answer generated successfully.',
                'answer' => $result['answer'],
                'sources' => $result['sources'],
            ]);

        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'Unable to generate answer.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
}