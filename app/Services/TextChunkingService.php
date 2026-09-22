<?php

namespace App\Services;

class TextChunkingService
{
    public function chunk(
        string $text,
        int $chunkSize = 900,
        int $overlap = 150
    ): array {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $words = preg_split('/\s+/', $text);

        if (!$words) {
            return [];
        }

        $chunks = [];

        $start = 0;
        $totalWords = count($words);

        while ($start < $totalWords) {
            $end = min(
                $start + $chunkSize,
                $totalWords
            );

            $chunkWords = array_slice(
                $words,
                $start,
                $end - $start
            );

            $chunkText = trim(
                implode(' ', $chunkWords)
            );

            if ($chunkText !== '') {
                $chunks[] = $chunkText;
            }

            if ($end >= $totalWords) {
                break;
            }

            $start = max(
                0,
                $end - $overlap
            );
        }

        return $chunks;
    }
}