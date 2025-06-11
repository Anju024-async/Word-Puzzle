<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Puzzle;
use App\Models\Student;
use App\Models\Submission;
use App\Models\HighScore;
use App\Services\WordPuzzleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Exception;

class WordPuzzleController extends Controller
{
    protected WordPuzzleService $service;

    /**
     * Inject the WordPuzzleService dependency.
     */
    public function __construct(WordPuzzleService $service)
    {
        $this->service = $service;
    }

    /**
     * Create a new puzzle with random letters.
     *
     * @return JsonResponse
     */
    public function createPuzzle(): JsonResponse
    {
        try {
            $letters = $this->generateRandomLetters(15);
            $puzzle = Puzzle::create(['letters' => $letters]);
            return response()->json(['puzzle_id' => $puzzle->id, 'letters' => $letters]);
        } catch (Exception $e) {
            // Log error if needed
            return response()->json(['error' => 'Failed to create puzzle'], 500);
        }
    }

    /**
     * Register a new student.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registerStudent(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string']);
        try {
            $student = Student::create(['name' => $request->name]);
            return response()->json(['student_id' => $student->id]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to register student'], 500);
        }
    }

    /**
     * Submit a word for a puzzle by a student.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitWord(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'puzzle_id' => 'required|exists:puzzles,id',
            'word' => 'required|string'
        ]);

        try {
            $student = Student::findOrFail($request->student_id);
            $puzzle = Puzzle::findOrFail($request->puzzle_id);
            $word = strtolower($request->word);

            // Gather all letters used by the student in this puzzle
            $usedLetters = '';
            foreach ($student->submissions()->where('puzzle_id', $puzzle->id)->get() as $submission) {
                $usedLetters .= $submission->word;
            }

            // Remove used letters from the puzzle's letters
            $availableLetters = $this->removeUsedLetters($puzzle->letters, $usedLetters);

            // Check if the word can be built from available letters
            if (!$this->service->canBuildWord($word, $availableLetters)) {
                return response()->json(['error' => 'Cannot build word from available letters'], 400);
            }

            // Check if the word is a valid English word
            if (!$this->service->isValidWord($word)) {
                return response()->json(['error' => 'Not a valid English word'], 400);
            }

            $score = $this->service->scoreWord($word);

            // Save submission and update high scores atomically
            DB::transaction(function () use ($student, $puzzle, $word, $score) {
                Submission::create([
                    'student_id' => $student->id,
                    'puzzle_id' => $puzzle->id,
                    'word' => $word,
                    'score' => $score
                ]);
                $this->service->updateHighScores($word, $score);
            });

            return response()->json(['score' => $score]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to submit word'], 500);
        }
    }

    /**
     * End the game for a student and return their score and possible remaining words.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function endGame(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'puzzle_id' => 'required|exists:puzzles,id'
        ]);

        try {
            $student = Student::findOrFail($request->student_id);
            $puzzle = Puzzle::findOrFail($request->puzzle_id);

            // Gather all letters used by the student in this puzzle
            $usedLetters = '';
            foreach ($student->submissions()->where('puzzle_id', $puzzle->id)->get() as $submission) {
                $usedLetters .= $submission->word;
            }
            $availableLetters = $this->removeUsedLetters($puzzle->letters, $usedLetters);

            // Find all possible words that can be built from remaining letters
            $possibleWords = [];
            foreach ($this->service->getDictionary() as $dictWord) {
                if ($this->service->canBuildWord($dictWord, $availableLetters)) {
                    $possibleWords[] = $dictWord;
                }
            }

            // Calculate total score for this puzzle
            $totalScore = $student->submissions()->where('puzzle_id', $puzzle->id)->sum('score');

            return response()->json([
                'score' => $totalScore,
                'remaining_letters' => $availableLetters,
                'possible_words' => $possibleWords
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to end game'], 500);
        }
    }

    /**
     * Get the leaderboard of top 10 high scores.
     *
     * @return JsonResponse
     */
    public function leaderboard(): JsonResponse
    {
        try {
            $scores = HighScore::orderByDesc('score')->orderBy('word')->take(10)->get(['word', 'score']);
            return response()->json($scores);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch leaderboard'], 500);
        }
    }

    /**
     * Generate a random string of letters of given length.
     *
     * @param int $length
     * @return string
     */  
    private function generateRandomLetters($length): string
    {
        $letters = '';
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < $length; $i++) {
            $letters .= $alphabet[random_int(0, 25)];
        }
        return $letters;
    }

    /**
     * Remove used letters from the available letters.
     *
     * @param string $letters
     * @param string $used
     * @return string
     */
    private function removeUsedLetters($letters, $used): string
    {
        $lettersArr = str_split($letters);
        foreach (str_split($used) as $char) {
            $index = array_search($char, $lettersArr);
            if ($index !== false) {
                unset($lettersArr[$index]);
            }
        }
        return implode('', $lettersArr);
    }
}