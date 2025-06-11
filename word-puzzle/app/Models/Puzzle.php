<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puzzle extends Model
{
    protected $fillable = ['letters'];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}