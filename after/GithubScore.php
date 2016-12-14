<?php

require_once __DIR__ . '/../lib/collection.php';

class GithubScore
{
    private $username;
    private $test;

    private function __construct($username, $test)
    {
        $this->username = $username;
        $this->test = $test;
    }

    public static function forUser($username, $test = false)
    {
        return (new self($username, $test))->score();
    }

    private function score()
    {
        return $this->events()->pluck('type')->map($this->lookupScore())->sum();
    }

    private function events()
    {
        if ($this->test) {
            $data = file_get_contents(__DIR__ . '/../data/' . $this->username . '.json');
        } else {
            $url = "https://api.github.com/users/{$this->username}/events";
            $context = stream_context_create(['http' => ['header' => "User-Agent: Kata Neoxia"]]);
            $data = file_get_contents($url, null, $context);
        }

        return collect(json_decode($data, true));
    }

    private function lookupScore()
    {
        return function ($eventType) {
            return collect([
                'PushEvent'          => 5,
                'CreateEvent'        => 4,
                'IssuesEvent'        => 3,
                'CommitCommentEvent' => 2,
            ])->get($eventType, 1);
        };
    }
}
