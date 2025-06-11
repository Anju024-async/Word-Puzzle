<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = ['student_id', 'puzzle_id', 'word', 'score'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function puzzle()
    {
        return $this->belongsTo(Puzzle::class);
    }
}