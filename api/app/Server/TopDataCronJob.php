<?php
/**
 * 后台顶部数据定时推送
 */

namespace App\Server;

use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;

class TopDataCronJob extends CronJob
{
    protected $isImmediate = true;

    protected $interval = 30000;

    public function run()
    {
        Task::deliver(new WsTask('topdata'), true);
    }
}
