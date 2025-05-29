<?php

namespace App;

function search(array $docs, string $query): array {
    $index = buildInvertedIndex($docs);
    $docWordFreqs = [];
    $docLengths = [];
    $totalDocs = count($docs);

    foreach ($docs as $doc) {
        $id = $doc['id'];
        $normalizedText = normalize($doc['text']);
        $words = preg_split('/\s+/', $normalizedText, -1, PREG_SPLIT_NO_EMPTY);
        $docWordFreqs[$id] = array_count_values($words);
        $docLengths[$id] = count($words);
    }

    $queryWords = preg_split('/\s+/', normalize($query), -1, PREG_SPLIT_NO_EMPTY);
    $scores = [];

    foreach ($queryWords as $word) {
        if (!isset($index[$word])) {
            continue;
        }

        $idf = log($totalDocs / count($index[$word]));

        foreach ($index[$word] as $docId) {
            $tf = ($docWordFreqs[$docId][$word] ?? 0) / $docLengths[$docId];
            $tfIdf = $tf * $idf;

            if (!isset($scores[$docId])) {
                $scores[$docId] = 0;
            }
            $scores[$docId] += $tfIdf;
        }
    }

    arsort($scores);

    return array_keys($scores);
}

function buildInvertedIndex(array $docs): array {
    $index = [];

    foreach ($docs as $doc) {
        $id = $doc['id'];
        $normalizedText = normalize($doc['text']);
        $words = preg_split('/\s+/', $normalizedText, -1, PREG_SPLIT_NO_EMPTY);
        $uniqueWords = array_unique($words);

        foreach ($uniqueWords as $word) {
            $index[$word][] = $id;
        }
    }

    return $index;
}

function normalize(string $text): string {
    return preg_replace('/[^\p{L}\p{N}\s]+/u', '', mb_strtolower($text));
}
