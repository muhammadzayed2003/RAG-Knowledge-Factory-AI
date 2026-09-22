<?php

namespace App\Http\Controllers;

use App\Models\WebsiteSource;
use App\Services\QdrantService;
use App\Services\WebsiteIngestionService;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function __construct(
        protected WebsiteIngestionService $websiteIngestionService,
        protected QdrantService $qdrantService
    ) {
    }

    public function index()
    {
        return response()->json([
            'websites' =>
                WebsiteSource::latest()
                    ->get(),
        ]);
    }

    public function store(
        Request $request
    ) {
        $validated =
            $request->validate([
                'url' => [
                    'required',
                    'string',
                    'max:2048',
                ],

                'max_pages' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],
            ]);

        $url =
            trim(
                $validated['url']
            );

        if (
            !preg_match(
                '#^https?://#i',
                $url
            )
        ) {
            $url =
                'https://'
                .$url;
        }

        $host =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_HOST
                )
            );

        $websiteSource =
            WebsiteSource::create([
                'url' =>
                    $url,

                'host' =>
                    $host,

                'page_count' =>
                    0,

                'chunk_count' =>
                    0,

                'status' =>
                    'pending',

                'error_message' =>
                    null,
            ]);

        try {
            $this->websiteIngestionService
                ->ingest(
                    $websiteSource,
                    $validated['max_pages']
                    ?? 50
                );

            $websiteSource
                ->refresh();

            return response()->json([
                'message' =>
                    'Website crawled and indexed successfully.',

                'website' =>
                    $websiteSource,
            ], 201);

        } catch (\Throwable $exception) {
            $websiteSource
                ->refresh();

            return response()->json([
                'message' =>
                    'Website ingestion failed.',

                'error' =>
                    $exception
                        ->getMessage(),

                'website' =>
                    $websiteSource,
            ], 500);
        }
    }

    public function destroy(
        WebsiteSource $websiteSource
    ) {
        try {
            $this->qdrantService
                ->deleteByWebsiteSourceId(
                    $websiteSource->id
                );

            $websiteSource
                ->delete();

            return response()->json([
                'message' =>
                    'Website knowledge deleted successfully.',
            ]);

        } catch (\Throwable $exception) {
            return response()->json([
                'message' =>
                    'Website deletion failed.',

                'error' =>
                    $exception
                        ->getMessage(),
            ], 500);
        }
    }
}