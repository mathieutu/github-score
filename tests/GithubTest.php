<?php

require_once __DIR__ . '/../before/github.php';
require_once __DIR__ . '/../after/GithubScore.php';
require_once __DIR__ . '/../after/GithubWinner.php';

class GithubTest extends PHPUnit_Framework_TestCase
{
    public function testScore()
    {
        $score = 8;
        $username = 'mathieutu';

        $this->assertEquals($score, githubScore($username, true)); // Before
        $this->assertEquals($score, GithubScore::forUser($username, true)); // After
    }

    public function testWinner()
    {
        $ranking = [
            ['username' => 'foo', 'score' => 15, 'rank' => 1,],
            ['username' => 'bar', 'score' => 15, 'rank' => 1,],
            ['username' => 'mathieutu', 'score' => 8, 'rank' => 3,],
        ];
        $usernames = ['foo', 'bar', 'mathieutu'];

        $this->assertEquals(json_encode($ranking), githubWinner($usernames, true)); // Before
        $this->assertEquals(json_encode($ranking), GithubWinner::find($usernames, true)); // After
    }
}
