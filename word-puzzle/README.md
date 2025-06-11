# Word Puzzle Async Challenge

This is a Laravel 12 backend service for processing student submissions for word puzzles, grading them, and presenting a high-score leaderboard.

## Features

- Generate random word puzzles for students.
- Students submit English words using puzzle letters.
- Each letter can only be used as many times as it appears in the puzzle.
- Words are validated against a dictionary (`resources/dictionary.txt`).
- Scores are calculated based on word length.
- High-score leaderboard (top 10, no duplicate words).
- API endpoints for all actions.

## Setup Instructions

### 1. Clone the Repository

```sh
git clone <your-repo-url>
cd word-puzzle
```

### 2. Install Dependencies

```sh
composer install
```

### 3. Environment Setup

Copy `.env.example` to `.env` and set up your environment variables.  
For quick local development, use SQLite:

```
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/your/project/database/database.sqlite
```

Create the SQLite file:

```sh
touch database/database.sqlite
```

### 4. Migrate the Database

```sh
php artisan migrate
```

### 5. Add a Dictionary

Add a file at `resources/dictionary.txt` with one valid English word per line, e.g.:

```
fox
dog
cat
kite
book
...
```

### 6. Run the Application

```sh
php artisan serve
```

### 7. API Usage

Use Postman, curl, or any HTTP client to interact with the API:

- **Create a puzzle:**  
  `POST /puzzle`

- **Register a student:**  
  `POST /student`  
  Body: `name=Alice`

- **Submit a word:**  
  `POST /submit`  
  Body: `student_id=1&puzzle_id=1&word=fox`

- **End the game:**  
  `POST /end`  
  Body: `student_id=1&puzzle_id=1`

- **Leaderboard:**  
  `GET /leaderboard`

### 8. Running Tests

```sh
php artisan test
```

## Solution Notes

- The service uses a simple text file for dictionary validation.
- All business logic is in `app/Services/WordPuzzleService.php`.
- Exception handling and validation are implemented in controllers.
- The project is designed for clarity, testability, and extensibility.

---

**Feel free to extend or adapt this project for your needs!**