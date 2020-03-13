<?php
return [
    'dsn' => env('SENTRY_DSN'),
    'breadcrumbs.sql_bindings' => true,
    'message_limit' => env('SENTRY_MESSAGE_LIMIT'),
];
