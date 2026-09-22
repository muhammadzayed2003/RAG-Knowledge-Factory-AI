<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class QdrantService
{
    protected string $baseUrl;
    protected string $collection;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.qdrant.url'),
            '/'
        );

        $this->collection = config(
            'services.qdrant.collection'
        );
    }

    public function createCollection(
        int $vectorSize
    ): array {
        $response = Http::put(
            "{$this->baseUrl}/collections/{$this->collection}",
            [
                'vectors' => [
                    'size' => $vectorSize,
                    'distance' => 'Cosine',
                ],
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to create Qdrant collection: '
                .$response->body()
            );
        }

        return $response->json();
    }

    public function collectionExists(): bool
    {
        $response = Http::get(
            "{$this->baseUrl}/collections/{$this->collection}"
        );

        return $response->successful();
    }

    public function upsertPoints(
        array $points
    ): array {
        $response = Http::timeout(180)
            ->put(
                "{$this->baseUrl}/collections/{$this->collection}/points?wait=true",
                [
                    'points' => $points,
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to insert points into Qdrant: '
                .$response->body()
            );
        }

        return $response->json();
    }

    public function search(
        array $vector,
        int $limit = 5
    ): array {
        $response = Http::post(
            "{$this->baseUrl}/collections/{$this->collection}/points/query",
            [
                'query' => $vector,
                'limit' => $limit,
                'with_payload' => true,
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to search Qdrant: '
                .$response->body()
            );
        }

        return $response->json(
            'result.points',
            []
        );
    }

    public function deleteByDocumentId(
        int $documentId
    ): array {
        if (!$this->collectionExists()) {
            return [
                'result' => true,
            ];
        }

        return $this->deleteByFilter(
            [
                [
                    'key' => 'document_id',
                    'match' => [
                        'value' => $documentId,
                    ],
                ],
            ]
        );
    }

    public function deleteByWebsiteSourceId(
        int $websiteSourceId
    ): array {
        if (!$this->collectionExists()) {
            return [
                'result' => true,
            ];
        }

        return $this->deleteByFilter(
            [
                [
                    'key' => 'website_source_id',
                    'match' => [
                        'value' => $websiteSourceId,
                    ],
                ],
            ]
        );
    }

    protected function deleteByFilter(
        array $must
    ): array {
        $response = Http::timeout(120)
            ->post(
                "{$this->baseUrl}/collections/{$this->collection}/points/delete?wait=true",
                [
                    'filter' => [
                        'must' => $must,
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to delete vectors from Qdrant: '
                .$response->body()
            );
        }

        return $response->json();
    }
}