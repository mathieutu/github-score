<?php

require_once __DIR__ . '/GithubWinner.php';
require_once __DIR__ . '/../data/github_users.php';

header('Content-Type: application/json');

echo GithubWinner::find(GITHUB_USERS);