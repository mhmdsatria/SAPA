<?php

namespace App\Services;

class CensorService
{
    public function censor(?string $text): string
    {
        $clean = trim((string) $text);

        if ($clean === '') {
            return '';
        }

        $words = array_filter(array_map('trim', config('censor.words', [])));
        $replacement = (string) config('censor.replacement', '***');

        usort($words, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        foreach ($words as $word) {
            $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($word, '/').'(?![\p{L}\p{N}])/iu';
            $clean = preg_replace($pattern, $replacement, $clean) ?? $clean;
        }

        return $clean;
    }

    public function containsProfanity(?string $text): bool
    {
        $original = trim((string) $text);

        return $original !== '' && $this->censor($original) !== $original;
    }
}
