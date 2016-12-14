<?php

require_once __DIR__ . '/../lib/collection.php';
require_once __DIR__ . '/../data/github_autorization.php';

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
            return $this->fetchEventsFromData();
        }

        return $this->fetchEventsFromGithub();
    }

    private function fetchEventsFromData()
    {
        $events = file_get_contents(__DIR__ . '/../data/' . $this->username . '.json');

        return collect(json_decode($events, true));
    }

    private function fetchEventsFromGithub()
    {
        $events = [];
        $page = 1;
        do {
            list($newEvents, $links) = $this->fetchEventsfromOneGithubPage($page);
            $events = array_merge($events, $newEvents);
            $page++;
        } while ($this->isThereANextPage($links));

        return collect($events);
    }

    private function fetchEventsfromOneGithubPage($page)
    {
        $context = stream_context_create([
            'http' => ['header' =>
                           "User-Agent: Github Score\r\n" .
                           "Authorization: Basic " . GITHUB_TOKEN,
            ]]);

        $url = "https://api.github.com/users/{$this->username}/events?page={$page}";
        $newEvents = json_decode(file_get_contents($url, null, $context));
        $links = $http_response_header[18];

        return [$newEvents, $links];
    }

    private function isThereANextPage($links)
    {
        return mb_strpos($links, 'next') !== false;
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
