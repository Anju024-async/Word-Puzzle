<?php

namespace Tests\Unit;

use App\Services\WordPuzzleService;
use Tests\TestCase; // <-- Use Laravel's TestCase

class WordPuzzleServiceTest extends TestCase
{
    public function test_can_build_word()
    {
        $service = new WordPuzzleService();
        $this->assertTrue($service->canBuildWord('fox', 'foxabc'));
        $this->assertFalse($service->canBuildWord('zebra', 'foxabc'));
    }

    public function test_score_word()
    {
        $service = new WordPuzzleService();
        $this->assertEquals(3, $service->scoreWord('fox'));
    }
}