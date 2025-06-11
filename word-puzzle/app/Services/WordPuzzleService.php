<?php

namespace App\Services;

use App\Models\HighScore;

class WordPuzzleService
{
    // For demo, use a simple array. Replace with a real dictionary or API.
    protected array $dictionary;

    public function __construct()
    {
        $this->dictionary = explode("\n", file_get_contents(resource_path('dictionary.txt')));
        $this->dictionary = array_map('trim', $this->dictionary);
    }

    public function getDictionary(): array
    {
        return $this->dictionary;
    }

    public function isValidWord(string $word): bool
    {
        return in_array(strtolower($word), $this->dictionary);
    }

    public function canBuildWord(string $word, string $letters): bool
    {
        $lettersArr = count_chars($letters, 1);
        $wordArr = count_chars($word, 1);

        foreach ($wordArr as $char => $count) {
            if (!isset($lettersArr[$char]) || $lettersArr[$char] < $count) {
                return false;
            }
        }
        return true;
    }

    public function scoreWord(string $word): int
    {
        return strlen($word);
    }

    public function updateHighScores(string $word, int $score): void
    {
        if (HighScore::where('word', $word)->exists()) {
            return;
        }
        HighScore::create(['word' => $word, 'score' => $score]);
        $this->trimHighScores();
    }

    protected function trimHighScores(): void
    {
        $scores = HighScore::orderByDesc('score')->orderBy('word')->get();
        if ($scores->count() > 10) {
            foreach ($scores->slice(10) as $score) {
                $score->delete();
            }
        }
    }
}