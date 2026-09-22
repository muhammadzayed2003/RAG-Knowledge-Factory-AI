<?php

namespace App\Services;

use RuntimeException;

class RagChatService
{
    public function __construct(
        protected GeminiService $gemini,
        protected QdrantService $qdrant
    ) {
    }

    public function ask(string $question, int $limit = 5): array
    {
        $question = trim($question);

        if ($question === '') {
            throw new RuntimeException(
                'Question cannot be empty.'
            );
        }

        $questionEmbedding = $this->gemini->createEmbedding(
            $question
        );

        $results = $this->qdrant->search(
            $questionEmbedding,
            $limit
        );

        if (empty($results)) {
            return [
                'answer' => 'I could not find that information in the available knowledge.',
                'sources' => [],
            ];
        }

        $contextParts = [];
        $sources = [];

        foreach ($results as $result) {
            $payload = $result['payload'] ?? [];

            $text = trim(
                (string) ($payload['text'] ?? '')
            );

            if ($text === '') {
                continue;
            }

            $fileName = (string) (
                $payload['file_name']
                ?? 'Unknown document'
            );

            $documentId =
                $payload['document_id'] ?? null;

            $chunkIndex =
                $payload['chunk_index'] ?? null;

            $score =
                $result['score'] ?? null;

            $contextParts[] =
                "Source: {$fileName}\n"
                ."Content:\n{$text}";

            $sources[] = [
                'document_id' => $documentId,
                'file_name' => $fileName,
                'chunk_index' => $chunkIndex,
                'score' => $score,
            ];
        }

        if (empty($contextParts)) {
            return [
                'answer' => 'I could not find that information in the available knowledge.',
                'sources' => [],
            ];
        }

        $context = implode(
            "\n\n---\n\n",
            $contextParts
        );

        $prompt = <<<PROMPT
You are IntelliAgent, a helpful conversational AI assistant.

Answer the user's question naturally and directly using the information provided below.

Important rules:

1. Give a straightforward conversational answer.
2. Do not mention RAG, retrieval, vector databases, Qdrant, embeddings, chunks, database searches, context retrieval, internal documents, or internal system processes.
3. Do not explain how you found the answer.
4. Do not say phrases such as "based on the provided context", "according to the retrieved information", or "according to the database".
5. Never expose internal technical implementation details unless the user specifically asks how the system works.
6. Do not use Markdown formatting.
7. Do not use asterisks, double asterisks, hashtags, backticks, Markdown headings, or Markdown bullet syntax.
8. Write plain clean text only.
9. Keep answers clear and concise unless the user asks for detail.
10. If the information is not available, simply say that you do not have enough information to answer that question.
11. Do not invent facts.
12. Do not unnecessarily introduce yourself in every response.
13. Respond like a normal intelligent chatbot having a direct conversation with the user.

Knowledge:

{$context}

User:

{$question}

Answer:
PROMPT;

        $answer = $this->gemini->generateAnswer(
            $prompt
        );

        $answer = $this->cleanAnswer(
            $answer
        );

        return [
            'answer' => $answer,
            'sources' => $sources,
        ];
    }

    protected function cleanAnswer(string $answer): string
    {
        $answer = str_replace(
            [
                '**',
                '__',
                '`',
            ],
            '',
            $answer
        );

        $answer = preg_replace(
            '/^\s*#{1,6}\s*/m',
            '',
            $answer
        );

        $answer = preg_replace(
            '/^\s*[-*]\s+/m',
            '',
            $answer
        );

        $answer = preg_replace(
            '/\*([^*]+)\*/',
            '$1',
            $answer
        );

        $answer = preg_replace(
            "/\n{3,}/",
            "\n\n",
            $answer
        );

        return trim($answer);
    }
}