<?php

require_once __DIR__ . '/../lib/collection.php';
require_once __DIR__ . '/GithubScore.php';

class GithubWinner
{
    private $usernames;
    private $test;

    private function __construct($usernames, $test)
    {
        $this->usernames = $usernames;
        $this->test = $test;
    }

    public static function find(array $usernames, $test = false)
    {
        return (new static($usernames, $test))->getWinner();
    }

    private function getWinner()
    {
        return collect($this->usernames)
            ->pipe($this->getScores())
            ->pipe($this->assignInitialRanking())
            ->pipe($this->adjustForSameScores())
            ->sortBy('rank')
            ->toJson(true);
    }

    private function getScores()
    {
        return function (Collection $scores) {
            return $scores->map(function ($username) {
                return (object) ['username' => $username, 'score' => GithubScore::forUser($username, $this->test)];
            })->sortByDesc('score');
        };
    }

    private function assignInitialRanking()
    {
        return function (Collection $scores) {
            return $scores->zip(range(1, $scores->count()))
                ->map(function ($scoreAndRank) {
                    list($score, $rank) = $scoreAndRank;

                    return (object) array_merge(
                        (array) $score,
                        compact('rank')
                    );
                });
        };
    }

    public function adjustForSameScores()
    {
        return function (Collection $scores) {
            return $scores->groupBy('score')
                ->map(function (Collection $sameScores) {
                    $lowestRank = $sameScores->pluck('rank')->min();

                    return $sameScores->map(function ($rankedScore) use ($lowestRank) {
                        return (object) array_merge((array) $rankedScore, ['rank' => $lowestRank]);
                    });
                })
                ->collapse();
        };
    }

}
