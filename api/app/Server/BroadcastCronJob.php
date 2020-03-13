<?php
/**
 * 广播
 */

namespace App\Server;

use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

class BroadcastCronJob extends CronJob
{
    protected $isImmediate = true;

    public function interval()
    {
        return config('server.broadcast_interval');
    }

    public function run()
    {
        Task::deliver(new WsTask('forexdata'), true);
    }
}
