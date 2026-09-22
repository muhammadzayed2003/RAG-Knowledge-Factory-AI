<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use RuntimeException;
use Smalot\PdfParser\Parser;
use ZipArchive;

class DocumentTextExtractorService
{
    public function __construct(
        protected GeminiService $gemini
    ) {
    }

    public function extract(
        string $filePath,
        string $extension,
        ?string $mimeType = null
    ): string {
        $extension =
            strtolower(
                $extension
            );

        $text = match ($extension) {
            'txt',
            'md',
            'csv',
            'json',
            'xml' =>
                $this->extractPlainFile(
                    $filePath
                ),

            'html',
            'htm' =>
                $this->extractHtml(
                    $filePath
                ),

            'pdf' =>
                $this->extractPdf(
                    $filePath,
                    $mimeType
                        ?: 'application/pdf'
                ),

            'docx' =>
                $this->extractDocx(
                    $filePath
                ),

            'xlsx' =>
                $this->extractXlsx(
                    $filePath
                ),

            'pptx' =>
                $this->extractPptx(
                    $filePath
                ),

            'jpg',
            'jpeg',
            'png',
            'webp' =>
                $this->extractImage(
                    $filePath,
                    $mimeType,
                    $extension
                ),

            default =>
                throw new RuntimeException(
                    "Unsupported file type: {$extension}"
                ),
        };

        $text =
            $this->cleanText(
                $text
            );

        if ($text === '') {
            throw new RuntimeException(
                'No readable knowledge could be extracted from this file.'
            );
        }

        return $text;
    }

    protected function extractPlainFile(
        string $filePath
    ): string {
        $contents =
            file_get_contents(
                $filePath
            );

        if ($contents === false) {
            throw new RuntimeException(
                'Unable to read text file.'
            );
        }

        return $contents;
    }

    protected function extractHtml(
        string $filePath
    ): string {
        $html =
            file_get_contents(
                $filePath
            );

        if ($html === false) {
            throw new RuntimeException(
                'Unable to read HTML file.'
            );
        }

        $html =
            preg_replace(
                '/<script\b[^>]*>.*?<\/script>/is',
                ' ',
                $html
            );

        $html =
            preg_replace(
                '/<style\b[^>]*>.*?<\/style>/is',
                ' ',
                $html
            );

        return html_entity_decode(
            strip_tags(
                $html
            ),
            ENT_QUOTES | ENT_HTML5
        );
    }

    protected function extractPdf(
        string $filePath,
        string $mimeType
    ): string {
        try {
            $parser =
                new Parser();

            $pdf =
                $parser->parseFile(
                    $filePath
                );

            $text =
                trim(
                    $pdf->getText()
                );

            if (
                mb_strlen($text)
                >= 50
            ) {
                return $text;
            }

        } catch (\Throwable $exception) {
            //
        }

        return $this->gemini
            ->extractKnowledgeFromFile(
                $filePath,
                $mimeType
            );
    }

    protected function extractDocx(
        string $filePath
    ): string {
        $phpWord =
            IOFactory::load(
                $filePath
            );

        $parts = [];

        foreach (
            $phpWord->getSections()
            as $section
        ) {
            foreach (
                $section->getElements()
                as $element
            ) {
                $this->collectPhpWordText(
                    $element,
                    $parts
                );
            }
        }

        return implode(
            "\n",
            $parts
        );
    }

    protected function collectPhpWordText(
        mixed $element,
        array &$parts
    ): void {
        if (
            method_exists(
                $element,
                'getText'
            )
        ) {
            try {
                $text =
                    $element->getText();

                if (
                    is_string($text)
                    && trim($text) !== ''
                ) {
                    $parts[] =
                        trim($text);
                }
            } catch (\Throwable $exception) {
                //
            }
        }

        if (
            method_exists(
                $element,
                'getElements'
            )
        ) {
            try {
                foreach (
                    $element->getElements()
                    as $child
                ) {
                    $this->collectPhpWordText(
                        $child,
                        $parts
                    );
                }
            } catch (\Throwable $exception) {
                //
            }
        }

        if (
            method_exists(
                $element,
                'getRows'
            )
        ) {
            try {
                foreach (
                    $element->getRows()
                    as $row
                ) {
                    if (
                        method_exists(
                            $row,
                            'getCells'
                        )
                    ) {
                        foreach (
                            $row->getCells()
                            as $cell
                        ) {
                            if (
                                method_exists(
                                    $cell,
                                    'getElements'
                                )
                            ) {
                                foreach (
                                    $cell->getElements()
                                    as $child
                                ) {
                                    $this->collectPhpWordText(
                                        $child,
                                        $parts
                                    );
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $exception) {
                //
            }
        }
    }

    protected function extractImage(
        string $filePath,
        ?string $mimeType,
        string $extension
    ): string {
        $mimeType =
            $mimeType
            ?: match ($extension) {
                'png' =>
                    'image/png',

                'webp' =>
                    'image/webp',

                default =>
                    'image/jpeg',
            };

        return $this->gemini
            ->extractKnowledgeFromFile(
                $filePath,
                $mimeType
            );
    }

    protected function extractPptx(
        string $filePath
    ): string {
        $zip =
            new ZipArchive();

        if (
            $zip->open($filePath)
            !== true
        ) {
            throw new RuntimeException(
                'Unable to open PPTX file.'
            );
        }

        $slides = [];

        for (
            $index = 0;
            $index < $zip->numFiles;
            $index++
        ) {
            $name =
                $zip->getNameIndex(
                    $index
                );

            if (
                !preg_match(
                    '#^ppt/slides/slide\d+\.xml$#',
                    $name
                )
            ) {
                continue;
            }

            $xml =
                $zip->getFromIndex(
                    $index
                );

            if ($xml === false) {
                continue;
            }

            preg_match_all(
                '/<a:t>(.*?)<\/a:t>/s',
                $xml,
                $matches
            );

            if (
                !empty(
                    $matches[1]
                )
            ) {
                $slideText =
                    array_map(
                        fn ($value) =>
                            html_entity_decode(
                                strip_tags($value),
                                ENT_QUOTES | ENT_XML1
                            ),
                        $matches[1]
                    );

                $slides[] =
                    implode(
                        ' ',
                        $slideText
                    );
            }
        }

        $zip->close();

        return implode(
            "\n\n",
            $slides
        );
    }

    protected function extractXlsx(
        string $filePath
    ): string {
        $zip =
            new ZipArchive();

        if (
            $zip->open($filePath)
            !== true
        ) {
            throw new RuntimeException(
                'Unable to open XLSX file.'
            );
        }

        $sharedStrings = [];

        $sharedXml =
            $zip->getFromName(
                'xl/sharedStrings.xml'
            );

        if ($sharedXml !== false) {
            preg_match_all(
                '/<t[^>]*>(.*?)<\/t>/s',
                $sharedXml,
                $matches
            );

            foreach (
                $matches[1] ?? []
                as $value
            ) {
                $sharedStrings[] =
                    html_entity_decode(
                        strip_tags($value),
                        ENT_QUOTES | ENT_XML1
                    );
            }
        }

        $sheetTexts = [];

        for (
            $index = 0;
            $index < $zip->numFiles;
            $index++
        ) {
            $name =
                $zip->getNameIndex(
                    $index
                );

            if (
                !preg_match(
                    '#^xl/worksheets/sheet\d+\.xml$#',
                    $name
                )
            ) {
                continue;
            }

            $xml =
                $zip->getFromIndex(
                    $index
                );

            if ($xml === false) {
                continue;
            }

            preg_match_all(
                '/<c([^>]*)>(.*?)<\/c>/s',
                $xml,
                $cells,
                PREG_SET_ORDER
            );

            $values = [];

            foreach (
                $cells
                as $cell
            ) {
                $attributes =
                    $cell[1] ?? '';

                $content =
                    $cell[2] ?? '';

                if (
                    !preg_match(
                        '/<v>(.*?)<\/v>/s',
                        $content,
                        $valueMatch
                    )
                ) {
                    continue;
                }

                $value =
                    html_entity_decode(
                        $valueMatch[1],
                        ENT_QUOTES | ENT_XML1
                    );

                if (
                    str_contains(
                        $attributes,
                        't="s"'
                    )
                ) {
                    $sharedIndex =
                        (int) $value;

                    $value =
                        $sharedStrings[
                            $sharedIndex
                        ]
                        ?? $value;
                }

                $values[] =
                    $value;
            }

            if (!empty($values)) {
                $sheetTexts[] =
                    implode(
                        ' | ',
                        $values
                    );
            }
        }

        $zip->close();

        return implode(
            "\n\n",
            $sheetTexts
        );
    }

    protected function cleanText(
        string $text
    ): string {
        $text =
            str_replace(
                "\0",
                '',
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
                '/[ \t]+/',
                ' ',
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

        return trim(
            $text
        );
    }
}