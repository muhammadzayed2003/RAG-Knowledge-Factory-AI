<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class RagIngestionService
{
    protected int $embeddingBatchSize = 20;

    public function __construct(
        protected DocumentTextExtractorService $extractor,
        protected TextChunkingService $chunker,
        protected GeminiService $gemini,
        protected QdrantService $qdrant
    ) {
    }

    public function ingest(
        Document $document
    ): void {
        $document->update([
            'status' =>
                'processing',

            'error_message' =>
                null,

            'chunk_count' =>
                0,
        ]);

        try {
            $absolutePath =
                Storage::disk('local')
                    ->path(
                        $document->file_path
                    );

            if (
                !file_exists(
                    $absolutePath
                )
            ) {
                throw new RuntimeException(
                    'Uploaded file was not found.'
                );
            }

            $extension =
                strtolower(
                    pathinfo(
                        $document->original_name,
                        PATHINFO_EXTENSION
                    )
                );

            $text =
                $this->extractor->extract(
                    $absolutePath,
                    $extension,
                    $document->mime_type
                );

            $chunks =
                $this->chunker->chunk(
                    $text
                );

            if (empty($chunks)) {
                throw new RuntimeException(
                    'No chunks could be generated from this file.'
                );
            }

            /*
             * If an earlier ingestion partially failed,
             * remove its old vectors before rebuilding.
             */
            if (
                $this->qdrant
                    ->collectionExists()
            ) {
                $this->qdrant
                    ->deleteByDocumentId(
                        $document->id
                    );
            }

            $chunkBatches =
                array_chunk(
                    $chunks,
                    $this->embeddingBatchSize,
                    true
                );

            $collectionChecked =
                false;

            $processedChunks =
                0;

            foreach (
                $chunkBatches
                as $chunkBatch
            ) {
                $chunkTexts =
                    array_values(
                        $chunkBatch
                    );

                $embeddings =
                    $this->gemini
                        ->createEmbeddings(
                            $chunkTexts
                        );

                if (
                    count($embeddings)
                    !== count($chunkTexts)
                ) {
                    throw new RuntimeException(
                        'Embedding batch size did not match chunk batch size.'
                    );
                }

                if (!$collectionChecked) {
                    $firstEmbedding =
                        $embeddings[0]
                        ?? [];

                    $vectorSize =
                        count(
                            $firstEmbedding
                        );

                    if ($vectorSize === 0) {
                        throw new RuntimeException(
                            'Gemini returned an empty embedding.'
                        );
                    }

                    if (
                        !$this->qdrant
                            ->collectionExists()
                    ) {
                        $this->qdrant
                            ->createCollection(
                                $vectorSize
                            );
                    }

                    $collectionChecked =
                        true;
                }

                $points = [];

                $batchIndexes =
                    array_keys(
                        $chunkBatch
                    );

                foreach (
                    $chunkTexts
                    as $batchPosition => $chunk
                ) {
                    $chunkIndex =
                        $batchIndexes[
                            $batchPosition
                        ];

                    $points[] = [
                        'id' =>
                            (string) Str::uuid(),

                        'vector' =>
                            $embeddings[
                                $batchPosition
                            ],

                        'payload' => [
                            'document_id' =>
                                $document->id,

                            'file_name' =>
                                $document->original_name,

                            'file_type' =>
                                $extension,

                            'source_type' =>
                                $this->sourceType(
                                    $extension
                                ),

                            'chunk_index' =>
                                $chunkIndex,

                            'text' =>
                                $chunk,
                        ],
                    ];
                }

                $this->qdrant
                    ->upsertPoints(
                        $points
                    );

                $processedChunks +=
                    count($points);

                /*
                 * Update the visible chunk count
                 * during long book ingestion.
                 */
                $document->update([
                    'chunk_count' =>
                        $processedChunks,
                ]);
            }

            $document->update([
                'status' =>
                    'ready',

                'chunk_count' =>
                    count($chunks),

                'error_message' =>
                    null,
            ]);

        } catch (\Throwable $exception) {
            $document->update([
                'status' =>
                    'failed',

                'error_message' =>
                    $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function sourceType(
        string $extension
    ): string {
        if (
            in_array(
                $extension,
                [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                ],
                true
            )
        ) {
            return 'image';
        }

        if (
            in_array(
                $extension,
                [
                    'xlsx',
                    'csv',
                ],
                true
            )
        ) {
            return 'spreadsheet';
        }

        if (
            $extension === 'pptx'
        ) {
            return 'presentation';
        }

        return 'document';
    }
}