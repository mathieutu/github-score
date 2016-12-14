<?php

function githubScore($username, $test = false)
{
    if ($test) {
        $data = file_get_contents(__DIR__ . '/../data/' . $username . '.json');
    } else {
        $url = "https://api.github.com/users/{$username}/events";
        $context = stream_context_create(['http' => ['header' => "User-Agent: Kata Neoxia"]]);
        $data = file_get_contents($url, null, $context);
    }

    $events = json_decode($data, true);

    $eventTypes = [];
    foreach ($events as $event) {
        $eventTypes[] = $event['type'];
    }
    $score = 0;
    foreach ($eventTypes as $eventType) {
        switch ($eventType) {
            case 'PushEvent':
                $score += 5;
                break;
            case 'CreateEvent':
                $score += 4;
                break;
            case 'IssuesEvent':
                $score += 3;
                break;
            case 'CommitCommentEvent':
                $score += 2;
                break;

            default:
                $score += 1;
                break;
        }
    }

    return $score;
}