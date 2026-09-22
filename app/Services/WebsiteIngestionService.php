<?php

namespace App\Services;

use App\Models\WebsiteSource;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class WebsiteIngestionService
{
    protected int $embeddingBatchSize = 20;

    protected int $maxPageCharacters = 150000;

    public function __construct(
        protected TextChunkingService $chunker,
        protected GeminiService $gemini,
        protected QdrantService $qdrant
    ) {
    }

    public function ingest(
        WebsiteSource $websiteSource,
        int $maxPages = 50
    ): void {
        $websiteSource->update([
            'status' => 'processing',
            'page_count' => 0,
            'chunk_count' => 0,
            'error_message' => null,
        ]);

        try {
            $startUrl = $this->normalizeUrl(
                $websiteSource->url
            );

            $this->ensurePublicUrl(
                $startUrl
            );

            $host = strtolower(
                (string) parse_url(
                    $startUrl,
                    PHP_URL_HOST
                )
            );

            if ($host === '') {
                throw new RuntimeException(
                    'Website host could not be determined.'
                );
            }

            $websiteSource->update([
                'url' => $startUrl,
                'host' => $host,
            ]);

            if (
                $this->qdrant
                    ->collectionExists()
            ) {
                $this->qdrant
                    ->deleteByWebsiteSourceId(
                        $websiteSource->id
                    );
            }

            $queue = [
                $startUrl,
            ];

            foreach (
                $this->discoverSitemapUrls(
                    $startUrl,
                    $host
                )
                as $sitemapUrl
            ) {
                if (
                    count($queue)
                    >= $maxPages
                ) {
                    break;
                }

                $queue[] =
                    $sitemapUrl;
            }

            $queue =
                array_values(
                    array_unique($queue)
                );

            $visited = [];

            $processedPages = 0;
            $processedChunks = 0;

            while (
                !empty($queue)
                && $processedPages < $maxPages
            ) {
                $url =
                    array_shift($queue);

                $normalizedUrl =
                    $this->normalizeUrl(
                        $url
                    );

                if (
                    isset(
                        $visited[
                            $normalizedUrl
                        ]
                    )
                ) {
                    continue;
                }

                $visited[
                    $normalizedUrl
                ] = true;

                if (
                    !$this->belongsToHost(
                        $normalizedUrl,
                        $host
                    )
                ) {
                    continue;
                }

                if (
                    !$this->isHtmlPageUrl(
                        $normalizedUrl
                    )
                ) {
                    continue;
                }

                try {
                    $page =
                        $this->fetchPage(
                            $normalizedUrl
                        );

                } catch (\Throwable $exception) {
                    continue;
                }

                if (
                    $page['text'] === ''
                ) {
                    continue;
                }

                $processedPages++;

                $chunks =
                    $this->chunker->chunk(
                        $page['text']
                    );

                if (!empty($chunks)) {
                    $processedChunks +=
                        $this->storePageChunks(
                            $websiteSource,
                            $normalizedUrl,
                            $page['title'],
                            $chunks
                        );
                }

                $websiteSource->update([
                    'page_count' =>
                        $processedPages,

                    'chunk_count' =>
                        $processedChunks,
                ]);

                foreach (
                    $page['links']
                    as $link
                ) {
                    if (
                        count($queue)
                        + count($visited)
                        >= $maxPages * 4
                    ) {
                        break;
                    }

                    if (
                        isset(
                            $visited[$link]
                        )
                    ) {
                        continue;
                    }

                    if (
                        !$this->belongsToHost(
                            $link,
                            $host
                        )
                    ) {
                        continue;
                    }

                    if (
                        !$this->isHtmlPageUrl(
                            $link
                        )
                    ) {
                        continue;
                    }

                    $queue[] =
                        $link;
                }

                $queue =
                    array_values(
                        array_unique($queue)
                    );
            }

            if ($processedPages === 0) {
                throw new RuntimeException(
                    'No readable public pages could be extracted from this website.'
                );
            }

            if ($processedChunks === 0) {
                throw new RuntimeException(
                    'Website pages were found, but no knowledge chunks could be created.'
                );
            }

            $websiteSource->update([
                'status' => 'ready',
                'page_count' =>
                    $processedPages,

                'chunk_count' =>
                    $processedChunks,

                'error_message' =>
                    null,
            ]);

        } catch (\Throwable $exception) {
            $websiteSource->update([
                'status' => 'failed',
                'error_message' =>
                    $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function storePageChunks(
        WebsiteSource $websiteSource,
        string $pageUrl,
        string $title,
        array $chunks
    ): int {
        $processed = 0;

        $batches =
            array_chunk(
                $chunks,
                $this->embeddingBatchSize,
                true
            );

        foreach (
            $batches
            as $chunkBatch
        ) {
            $texts =
                array_values(
                    $chunkBatch
                );

            $embeddings =
                $this->gemini
                    ->createEmbeddings(
                        $texts
                    );

            if (
                count($embeddings)
                !== count($texts)
            ) {
                throw new RuntimeException(
                    'Website embedding batch returned an invalid result.'
                );
            }

            if (
                !$this->qdrant
                    ->collectionExists()
            ) {
                $firstVector =
                    $embeddings[0]
                    ?? [];

                if (empty($firstVector)) {
                    throw new RuntimeException(
                        'Website embedding vector was empty.'
                    );
                }

                $this->qdrant
                    ->createCollection(
                        count($firstVector)
                    );
            }

            $indexes =
                array_keys(
                    $chunkBatch
                );

            $points = [];

            foreach (
                $texts
                as $position => $text
            ) {
                $chunkIndex =
                    $indexes[
                        $position
                    ];

                $points[] = [
                    'id' =>
                        (string) Str::uuid(),

                    'vector' =>
                        $embeddings[
                            $position
                        ],

                    'payload' => [
                        'website_source_id' =>
                            $websiteSource->id,

                        'source_type' =>
                            'website',

                        'source_url' =>
                            $pageUrl,

                        'website_host' =>
                            $websiteSource->host,

                        'page_title' =>
                            $title,

                        /*
                         * Existing RagChatService already
                         * expects file_name.
                         * Giving it a readable website source
                         * means no chat changes are required.
                         */
                        'file_name' =>
                            $title !== ''
                            ? $title
                            : $websiteSource->host,

                        'chunk_index' =>
                            $chunkIndex,

                        'text' =>
                            $text,
                    ],
                ];
            }

            $this->qdrant
                ->upsertPoints(
                    $points
                );

            $processed +=
                count($points);
        }

        return $processed;
    }

    protected function fetchPage(
        string $url
    ): array {
        $this->ensurePublicUrl(
            $url
        );

        $response =
            Http::timeout(25)
                ->withOptions([
                    'allow_redirects' =>
                        false,
                ])
                ->withHeaders([
                    'User-Agent' =>
                        'RAGKnowledgeCrawler/1.0',

                    'Accept' =>
                        'text/html,application/xhtml+xml',
                ])
                ->get($url);

        if (
            $response->status()
            >= 300
            && $response->status()
            < 400
        ) {
            $location =
                $response->header(
                    'Location'
                );

            if (!$location) {
                throw new RuntimeException(
                    'Website redirected without a destination.'
                );
            }

            $redirectUrl =
                $this->resolveUrl(
                    $url,
                    $location
                );

            $this->ensurePublicUrl(
                $redirectUrl
            );

            $response =
                Http::timeout(25)
                    ->withOptions([
                        'allow_redirects' =>
                            false,
                    ])
                    ->withHeaders([
                        'User-Agent' =>
                            'RAGKnowledgeCrawler/1.0',

                        'Accept' =>
                            'text/html,application/xhtml+xml',
                    ])
                    ->get(
                        $redirectUrl
                    );
        }

        if (!$response->successful()) {
            throw new RuntimeException(
                "Unable to fetch website page: {$url}"
            );
        }

        $contentType =
            strtolower(
                (string) $response
                    ->header(
                        'Content-Type'
                    )
            );

        if (
            $contentType !== ''
            && !str_contains(
                $contentType,
                'text/html'
            )
            && !str_contains(
                $contentType,
                'application/xhtml+xml'
            )
        ) {
            throw new RuntimeException(
                'Page is not HTML.'
            );
        }

        $html =
            $response->body();

        if (
            trim($html) === ''
        ) {
            throw new RuntimeException(
                'Website returned an empty page.'
            );
        }

        return $this->parseHtml(
            $html,
            $url
        );
    }

    protected function parseHtml(
        string $html,
        string $pageUrl
    ): array {
        libxml_use_internal_errors(
            true
        );

        $document =
            new DOMDocument();

        $document->loadHTML(
            '<?xml encoding="UTF-8">'
            .$html,
            LIBXML_NOERROR
            | LIBXML_NOWARNING
        );

        libxml_clear_errors();

        $xpath =
            new DOMXPath(
                $document
            );

        foreach (
            [
                '//script',
                '//style',
                '//noscript',
                '//svg',
                '//nav',
                '//footer',
                '//iframe',
                '//form',
            ]
            as $query
        ) {
            $nodes =
                $xpath->query(
                    $query
                );

            if (!$nodes) {
                continue;
            }

            foreach (
                iterator_to_array(
                    $nodes
                )
                as $node
            ) {
                if ($node->parentNode) {
                    $node->parentNode
                        ->removeChild(
                            $node
                        );
                }
            }
        }

        $title = '';

        $titleNodes =
            $document
                ->getElementsByTagName(
                    'title'
                );

        if (
            $titleNodes->length > 0
        ) {
            $title =
                $this->cleanText(
                    $titleNodes
                        ->item(0)
                        ->textContent
                );
        }

        $mainNodes =
            $xpath->query(
                '//main'
            );

        if (
            $mainNodes
            && $mainNodes->length > 0
        ) {
            $rawText =
                $mainNodes
                    ->item(0)
                    ->textContent;
        } else {
            $bodyNodes =
                $document
                    ->getElementsByTagName(
                        'body'
                    );

            $rawText =
                $bodyNodes->length > 0
                ? $bodyNodes
                    ->item(0)
                    ->textContent
                : $document
                    ->textContent;
        }

        $text =
            $this->cleanText(
                $rawText
            );

        if (
            mb_strlen($text)
            > $this->maxPageCharacters
        ) {
            $text =
                mb_substr(
                    $text,
                    0,
                    $this->maxPageCharacters
                );
        }

        $links = [];

        foreach (
            $document
                ->getElementsByTagName(
                    'a'
                )
            as $anchor
        ) {
            $href =
                trim(
                    $anchor->getAttribute(
                        'href'
                    )
                );

            if ($href === '') {
                continue;
            }

            if (
                str_starts_with(
                    strtolower($href),
                    'mailto:'
                )
                || str_starts_with(
                    strtolower($href),
                    'tel:'
                )
                || str_starts_with(
                    strtolower($href),
                    'javascript:'
                )
            ) {
                continue;
            }

            $resolved =
                $this->resolveUrl(
                    $pageUrl,
                    $href
                );

            if ($resolved !== '') {
                $links[] =
                    $resolved;
            }
        }

        return [
            'title' =>
                $title,

            'text' =>
                $text,

            'links' =>
                array_values(
                    array_unique(
                        $links
                    )
                ),
        ];
    }

    protected function discoverSitemapUrls(
        string $startUrl,
        string $host
    ): array {
        $scheme =
            parse_url(
                $startUrl,
                PHP_URL_SCHEME
            )
            ?: 'https';

        $sitemapUrl =
            "{$scheme}://{$host}/sitemap.xml";

        try {
            $this->ensurePublicUrl(
                $sitemapUrl
            );

            $response =
                Http::timeout(15)
                    ->withOptions([
                        'allow_redirects' =>
                            false,
                    ])
                    ->withHeaders([
                        'User-Agent' =>
                            'RAGKnowledgeCrawler/1.0',
                    ])
                    ->get(
                        $sitemapUrl
                    );

            if (
                !$response
                    ->successful()
            ) {
                return [];
            }

            $xml =
                @simplexml_load_string(
                    $response->body()
                );

            if ($xml === false) {
                return [];
            }

            $urls = [];

            if (isset($xml->url)) {
                foreach (
                    $xml->url
                    as $item
                ) {
                    $url =
                        trim(
                            (string) $item->loc
                        );

                    if (
                        $url !== ''
                        && $this->belongsToHost(
                            $url,
                            $host
                        )
                    ) {
                        $urls[] =
                            $this->normalizeUrl(
                                $url
                            );
                    }
                }
            }

            return array_values(
                array_unique($urls)
            );

        } catch (\Throwable $exception) {
            return [];
        }
    }

    protected function normalizeUrl(
        string $url
    ): string {
        $url =
            trim($url);

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

        $parts =
            parse_url($url);

        if (
            !$parts
            || empty($parts['host'])
        ) {
            throw new RuntimeException(
                'Invalid website URL.'
            );
        }

        $scheme =
            strtolower(
                $parts['scheme']
                ?? 'https'
            );

        if (
            !in_array(
                $scheme,
                [
                    'http',
                    'https',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Only HTTP and HTTPS websites are supported.'
            );
        }

        $host =
            strtolower(
                $parts['host']
            );

        $port =
            isset($parts['port'])
            ? ':'.$parts['port']
            : '';

        $path =
            $parts['path']
            ?? '/';

        if ($path === '') {
            $path = '/';
        }

        $path =
            preg_replace(
                '#/+#',
                '/',
                $path
            );

        $query =
            isset($parts['query'])
            && $parts['query'] !== ''
            ? '?'.$parts['query']
            : '';

        return
            "{$scheme}://{$host}{$port}{$path}{$query}";
    }

    protected function resolveUrl(
        string $baseUrl,
        string $href
    ): string {
        $href =
            trim($href);

        if ($href === '') {
            return '';
        }

        if (
            str_starts_with(
                $href,
                '#'
            )
        ) {
            return '';
        }

        if (
            preg_match(
                '#^https?://#i',
                $href
            )
        ) {
            try {
                return $this->normalizeUrl(
                    $href
                );
            } catch (\Throwable $exception) {
                return '';
            }
        }

        $base =
            parse_url(
                $baseUrl
            );

        if (
            !$base
            || empty($base['host'])
        ) {
            return '';
        }

        $scheme =
            $base['scheme']
            ?? 'https';

        $host =
            $base['host'];

        $port =
            isset($base['port'])
            ? ':'.$base['port']
            : '';

        if (
            str_starts_with(
                $href,
                '//'
            )
        ) {
            return $this->normalizeUrl(
                $scheme.':'.$href
            );
        }

        if (
            str_starts_with(
                $href,
                '/'
            )
        ) {
            return $this->normalizeUrl(
                "{$scheme}://{$host}{$port}{$href}"
            );
        }

        $basePath =
            $base['path']
            ?? '/';

        $directory =
            rtrim(
                dirname($basePath),
                '/\\'
            );

        if ($directory === '.') {
            $directory = '';
        }

        $combined =
            "{$directory}/{$href}";

        $segments = [];

        foreach (
            explode(
                '/',
                $combined
            )
            as $segment
        ) {
            if (
                $segment === ''
                || $segment === '.'
            ) {
                continue;
            }

            if (
                $segment === '..'
            ) {
                array_pop(
                    $segments
                );

                continue;
            }

            $segments[] =
                $segment;
        }

        $path =
            '/'
            .implode(
                '/',
                $segments
            );

        return $this->normalizeUrl(
            "{$scheme}://{$host}{$port}{$path}"
        );
    }

    protected function belongsToHost(
        string $url,
        string $host
    ): bool {
        $urlHost =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_HOST
                )
            );

        return
            $this->canonicalHost(
                $urlHost
            )
            ===
            $this->canonicalHost(
                $host
            );
    }

    protected function canonicalHost(
        string $host
    ): string {
        return preg_replace(
            '/^www\./i',
            '',
            strtolower($host)
        );
    }

    protected function isHtmlPageUrl(
        string $url
    ): bool {
        $path =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_PATH
                )
            );

        $blockedExtensions = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'svg',
            'pdf',
            'zip',
            'rar',
            '7z',
            'mp3',
            'mp4',
            'avi',
            'mov',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'css',
            'js',
            'xml',
        ];

        $extension =
            pathinfo(
                $path,
                PATHINFO_EXTENSION
            );

        return
            $extension === ''
            || !in_array(
                $extension,
                $blockedExtensions,
                true
            );
    }

    protected function ensurePublicUrl(
        string $url
    ): void {
        $host =
            (string) parse_url(
                $url,
                PHP_URL_HOST
            );

        if ($host === '') {
            throw new RuntimeException(
                'Invalid website host.'
            );
        }

        if (
            strtolower($host)
            === 'localhost'
        ) {
            throw new RuntimeException(
                'Local/private websites cannot be crawled.'
            );
        }

        $ips =
            gethostbynamel(
                $host
            );

        if (
            $ips === false
            || empty($ips)
        ) {
            throw new RuntimeException(
                'Website host could not be resolved.'
            );
        }

        foreach ($ips as $ip) {
            $isPublic =
                filter_var(
                    $ip,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE
                    | FILTER_FLAG_NO_RES_RANGE
                );

            if ($isPublic === false) {
                throw new RuntimeException(
                    'Private or reserved network addresses cannot be crawled.'
                );
            }
        }
    }

    protected function cleanText(
        string $text
    ): string {
        $text =
            html_entity_decode(
                $text,
                ENT_QUOTES
                | ENT_HTML5
            );

        $text =
            str_replace(
                "\0",
                '',
                $text
            );

        $text =
            preg_replace(
                '/[ \t]+/',
                ' ',
                $text
            );

        $text =
            preg_replace(
                "/\r\n|\r/",
                "\n",
                $text
            );

        $text =
            preg_replace(
                "/\n[ \t]+/",
                "\n",
                $text
            );

        $text =
            preg_replace(
                "/\n{3,}/",
                "\n\n",
                $text
            );

        return trim($text);
    }
}