<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $chatModel;
    protected string $embeddingModel;

    public function __construct()
    {
        $this->baseUrl =
            'https://generativelanguage.googleapis.com/v1beta';

        $this->apiKey =
            (string) config(
                'services.gemini.api_key'
            );

        $this->chatModel =
            (string) config(
                'services.gemini.chat_model'
            );

        $this->embeddingModel =
            (string) config(
                'services.gemini.embedding_model'
            );

        if ($this->apiKey === '') {
            throw new RuntimeException(
                'Gemini API key is missing.'
            );
        }
    }

    public function generateAnswer(
        string $prompt
    ): string {
        $response = Http::timeout(180)
            ->withHeaders([
                'x-goog-api-key' =>
                    $this->apiKey,

                'Content-Type' =>
                    'application/json',
            ])
            ->post(
                "{$this->baseUrl}/models/{$this->chatModel}:generateContent",
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' =>
                                        $prompt,
                                ],
                            ],
                        ],
                    ],

                    'generationConfig' => [
                        'temperature' =>
                            0.2,
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini generation failed: '
                .$response->body()
            );
        }

        $text =
            $response->json(
                'candidates.0.content.parts.0.text'
            );

        if (
            !is_string($text)
            || trim($text) === ''
        ) {
            throw new RuntimeException(
                'Gemini returned an empty response.'
            );
        }

        return trim($text);
    }

    public function createEmbedding(
        string $text
    ): array {
        $response = Http::timeout(180)
            ->withHeaders([
                'x-goog-api-key' =>
                    $this->apiKey,

                'Content-Type' =>
                    'application/json',
            ])
            ->post(
                "{$this->baseUrl}/models/{$this->embeddingModel}:embedContent",
                [
                    'model' =>
                        "models/{$this->embeddingModel}",

                    'content' => [
                        'parts' => [
                            [
                                'text' =>
                                    $text,
                            ],
                        ],
                    ],

                    'outputDimensionality' =>
                        768,
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini embedding failed: '
                .$response->body()
            );
        }

        $values =
            $response->json(
                'embedding.values'
            );

        if (
            !is_array($values)
            || empty($values)
        ) {
            throw new RuntimeException(
                'Gemini returned an empty embedding.'
            );
        }

        return $values;
    }

    public function createEmbeddings(
        array $texts
    ): array {
        $texts =
            array_values(
                array_filter(
                    $texts,
                    fn ($text) =>
                        is_string($text)
                        && trim($text) !== ''
                )
            );

        if (empty($texts)) {
            return [];
        }

        $requests = [];

        foreach ($texts as $text) {
            $requests[] = [
                'model' =>
                    "models/{$this->embeddingModel}",

                'content' => [
                    'parts' => [
                        [
                            'text' =>
                                $text,
                        ],
                    ],
                ],

                'outputDimensionality' =>
                    768,
            ];
        }

        $response = Http::timeout(300)
            ->withHeaders([
                'x-goog-api-key' =>
                    $this->apiKey,

                'Content-Type' =>
                    'application/json',
            ])
            ->post(
                "{$this->baseUrl}/models/{$this->embeddingModel}:batchEmbedContents",
                [
                    'requests' =>
                        $requests,
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini batch embedding failed: '
                .$response->body()
            );
        }

        $embeddings =
            $response->json(
                'embeddings'
            );

        if (
            !is_array($embeddings)
            || count($embeddings)
                !== count($texts)
        ) {
            throw new RuntimeException(
                'Gemini returned an invalid embedding batch.'
            );
        }

        $vectors = [];

        foreach ($embeddings as $embedding) {
            $values =
                $embedding['values']
                ?? [];

            if (
                !is_array($values)
                || empty($values)
            ) {
                throw new RuntimeException(
                    'Gemini returned an empty vector inside a batch.'
                );
            }

            $vectors[] =
                $values;
        }

        return $vectors;
    }

    public function extractKnowledgeFromFile(
        string $filePath,
        string $mimeType
    ): string {
        if (!file_exists($filePath)) {
            throw new RuntimeException(
                'File was not found.'
            );
        }

        $contents =
            file_get_contents(
                $filePath
            );

        if ($contents === false) {
            throw new RuntimeException(
                'Unable to read file.'
            );
        }

        $encodedFile =
            base64_encode(
                $contents
            );

        $prompt = <<<PROMPT
Extract all useful factual knowledge from this file.

Instructions:

1. Read all visible and understandable information.
2. Preserve names, dates, numbers, titles, prices, contact information, technical details, tables and relationships accurately.
3. If this is an image, understand both visible text and useful visual information.
4. If this is a scanned document, read the visible document content.
5. Convert tables into understandable plain text while preserving their information.
6. Do not invent facts.
7. Do not summarize away important details.
8. Return clean plain text suitable for splitting into retrieval chunks.
9. Do not use Markdown formatting.
10. Do not explain the extraction process.

Return only the extracted knowledge.
PROMPT;

        $response = Http::timeout(300)
            ->withHeaders([
                'x-goog-api-key' =>
                    $this->apiKey,

                'Content-Type' =>
                    'application/json',
            ])
            ->post(
                "{$this->baseUrl}/models/{$this->chatModel}:generateContent",
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' =>
                                        $prompt,
                                ],

                                [
                                    'inlineData' => [
                                        'mimeType' =>
                                            $mimeType,

                                        'data' =>
                                            $encodedFile,
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'generationConfig' => [
                        'temperature' =>
                            0.1,
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini file extraction failed: '
                .$response->body()
            );
        }

        $text =
            $response->json(
                'candidates.0.content.parts.0.text'
            );

        if (
            !is_string($text)
            || trim($text) === ''
        ) {
            throw new RuntimeException(
                'Gemini could not extract knowledge from this file.'
            );
        }

        return trim($text);
    }
}