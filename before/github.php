<?php

require_once __DIR__ . '/../data/github_autorization.php';

function githubWinner(array $usernames, $test = false)
{
    $scores = [];
    foreach ($usernames as $username) {
        $scores[] = (object) ['username' => $username, 'score' => githubScore($username, $test)];
    }

    $ranks = [];

    foreach ($scores as $key => $score) {
        $ranks[$key] = $score->score;
    }

    arsort($ranks);

    $rank = 1;
    foreach ($ranks as $key => $score) {
        $ranks[$key] = $scores[$key];
        $ranks[$key]->rank = $rank;
        $rank++;
    }

    $ranks = array_values($ranks);

    foreach ($ranks as $key => $score) {
        if (isset($ranks[$key - 1])) {
            $prev_score = $ranks[$key - 1];
            if ($score->score === $prev_score->score) {
                $score->rank = $prev_score->rank;
            }
        }
    }

    return json_encode($ranks, true);

}

function githubScore($username, $test = false)
{
    if ($test) {
        $data = file_get_contents(__DIR__ . '/../data/' . $username . '.json');

       $events = json_decode($data, true);
    } else {
        $context = stream_context_create([
            'http' => ['header' =>
                           "User-Agent: Github Score\r\n" .
                           'Authorization: Basic ' . GITHUB_TOKEN,
            ]]);

        $events = [];
        $page = 1;
        do {
            $url = "https://api.github.com/users/{$username}/events?page={$page}";
            $events = array_merge($events, json_decode(file_get_contents($url, null, $context), true));
            $page++;
        } while (mb_strpos($http_response_header[18], 'next') !== false);
    }

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