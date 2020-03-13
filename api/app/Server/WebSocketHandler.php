<?php
/**
 * Description
 */

namespace App\Server;

use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Illuminate\Support\Facades\Log;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebSocketHandler implements WebSocketHandlerInterface
{
    public function __construct()
    {
    }

    public function onOpen(Server $server, Request $request)
    {
        // 5秒后检查是否登录
        $server->after(5000, function () use ($request) {
            Task::deliver(new WsTask('checkLogin', $request->fd));
        });

        Log::channel('ws')->info('New user has entered', ['fd' => $request->fd]);
    }

    public function onMessage(Server $server, Frame $frame)
    {
        $data = json_decode($frame->data, true);
        if (!isset($data['action'])) {
            return;
        }

        Log::channel('ws')->info('new message', ['fd' => $frame->fd, 'data' => $data]);

        // 登录
        if ($data['action'] == 'login') {
            Task::deliver(new WsTask('login', $frame->fd, $data['token'] ?? '', (int) $data['type'] ?? Util::TYPE_GBPAUD));
        }
    }

    public function onClose(Server $server, $fd, $reactorId)
    {
        Task::deliver(new WsTask('delUser', $fd, '0'));
        Log::channel('ws')->info('User has logged out', ['fd' => $fd]);
    }
}
