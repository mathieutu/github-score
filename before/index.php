<?php

require_once __DIR__ . '/github.php';
require_once __DIR__ . '/../data/github_users.php';

header('Content-Type: application/json');

echo githubWinner(GITHUB_USERS);