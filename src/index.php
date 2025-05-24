<?php

namespace App;

function search(array $docs, string $query): array {
    $queryWords = preg_split('/\s+/', normalize($query), -1, PREG_SPLIT_NO_EMPTY);
    $result = [];

    foreach ($docs as $doc) {
        $normalizedText = normalize($doc['text']);
        $words = preg_split('/\s+/', $normalizedText, -1, PREG_SPLIT_NO_EMPTY);

        $wordCounts = array_count_values($words);
        $matchedWordsCount = 0;
        $totalHits = 0;

        foreach ($queryWords as $word) {
            if (isset($wordCounts[$word])) {
                $matchedWordsCount++;
                $totalHits += $wordCounts[$word];
            }
        }

        if ($matchedWordsCount > 0) {
            $result[] = [
                'id' => $doc['id'],
                'matched' => $matchedWordsCount,
                'hits' => $totalHits,
            ];
        }
    }

    usort($result, function ($a, $b) {
        return [$b['matched'], $b['hits']] <=> [$a['matched'], $a['hits']];
    });

    return array_column($result, 'id');
}

function normalize(string $text): string {
    return preg_replace('/[^\p{L}\p{N}\s]+/u', '', mb_strtolower($text));
}