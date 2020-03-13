<?php
use App\Server\BroadcastCronJob;
use App\Server\MockCronJob;
use App\Server\PullProcess;
use App\Server\TopDataCronJob;
use App\Server\WebSocketHandler;

// 定时脚本任务
$cron_jobs = [
    BroadcastCronJob::class,
    TopDataCronJob::class,
];
if (env('SERVER_PULL_MOCK', false)) {
    $cron_jobs[] = MockCronJob::class;
}

// 是否开启拉取
$processes = [];
if (env('SERVER_PULL_INTERVAL') > 0) {
    $processes[] = PullProcess::class;
}

return [
    'listen_ip' => env('SERVER_LISTEN_SOCK', storage_path('server/main.sock')),
    'socket_type' => SWOOLE_UNIX_STREAM,
    'enable_coroutine_runtime' => false,
    'server' => env('LARAVELS_SERVER', 'FXHOS'),
    'handle_static' => env('LARAVELS_HANDLE_STATIC', false),
    'laravel_base_path' => env('LARAVEL_BASE_PATH', base_path()),
    'inotify_reload' => [
        'enable' => env('LARAVELS_INOTIFY_RELOAD', false),
        'watch_path' => base_path(),
        'file_types' => ['.php'],
        'excluded_dirs' => [],
        'log' => true,
    ],
    'websocket' => [
        'enable' => true,
        'handler' => WebSocketHandler::class,
    ],
    'sockets' => [],
    'processes' => $processes,
    'timer' => [
        'enable' => true,
        'jobs' => $cron_jobs,
        'pid_file' => env('SERVER_TIME_PID', storage_path('server/timer.pid')),
        'max_wait_time' => 5,
    ],
    'events' => [],
    'swoole_tables' => [],
    'register_providers' => [],
    'cleaners' => [
        Hhxsv5\LaravelS\Illuminate\Cleaners\JWTCleaner::class,
    ],
    'swoole' => [
        'daemonize' => env('LARAVELS_DAEMONIZE', false),
        'dispatch_mode' => 2,
        'reactor_num' => function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 4,
        'worker_num' => function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 8,
        'task_worker_num' => function_exists('swoole_cpu_num') ? swoole_cpu_num() * 2 : 8,
        'task_ipc_mode' => 1,
        'task_max_request' => 8000,
        'task_tmpdir' => @is_writable('/dev/shm/') ? '/dev/shm' : '/tmp',
        'max_request' => 8000,
        'open_tcp_nodelay' => true,
        'pid_file' => env('SERVER_MASTER_PID', storage_path('server/master.pid')),
        'log_file' => storage_path(sprintf('server/swoole-%s.log', date('Y-m'))),
        'log_level' => 4,
        'document_root' => base_path('public'),
        'buffer_output_size' => 2 * 1024 * 1024,
        'socket_buffer_size' => 128 * 1024 * 1024,
        'package_max_length' => 4 * 1024 * 1024,
        'reload_async' => true,
        'max_wait_time' => 60,
        'enable_reuse_port' => true,
        'enable_coroutine' => false,
        'http_compression' => false,
    ],
];
