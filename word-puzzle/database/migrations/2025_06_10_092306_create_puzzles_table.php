<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('puzzles', function (Blueprint $table) {
            $table->id();
            $table->string('letters');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('puzzles');
    }
};