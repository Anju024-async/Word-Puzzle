<?php
use App\Http\Controllers\WordPuzzleController;
use Illuminate\Support\Facades\Route;

Route::post('/puzzle', [WordPuzzleController::class, 'createPuzzle']);
Route::post('/student', [WordPuzzleController::class, 'registerStudent']);
Route::post('/submit', [WordPuzzleController::class, 'submitWord']);
Route::post('/end', [WordPuzzleController::class, 'endGame']);
Route::get('/leaderboard', [WordPuzzleController::class, 'leaderboard']);
Route::get('/test', function () {
    return 'API is working';
});