<?php
return [
    'broadcast_interval' => env('SERVER_BROADCAST_INTERVAL', 1000),
    'pull_interval' => env('SERVER_PULL_INTERVAL', 1000),
    'pull_url' => env('SERVER_PULL_URL'),
    'pull_mock' => env('SERVER_PULL_MOCK', false),
];
 