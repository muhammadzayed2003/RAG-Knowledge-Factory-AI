<?php

namespace App\Http\Controllers;

use App\Services\RagChatService;
use Illuminate\Http\Request;

class PublicChatController extends Controller
{
    public function __construct(
        protected RagChatService $ragChatService
    ) {
    }

    public function show()
    {
        return view('public-chat');
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