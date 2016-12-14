<?php

require_once __DIR__ . '/../before/github.php';

class GithubTest extends PHPUnit_Framework_TestCase
{
    public function testScore()
    {
        $score = 8;
        $username = 'mathieutu';

        $this->assertEquals($score, githubScore($username, true));
    }
}
