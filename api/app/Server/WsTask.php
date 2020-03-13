<?php
/**
 * 任务处理
 */

namespace App\Server;

use App\Chat;
use App\Models\Scene;
use App\User;
use Carbon\Carbon;
use Closure;
use Exception;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;
use Tymon\JWTAuth\JWTAuth;

class WsTask extends Task
{
    private $method;
    private $params;

    public function __construct(string $method, ...$params)
    {
        $this->method = $method;
        $this->params = $params;
    }

    public function handle()
    {
        // 分发任务
        if (method_exists($this, $this->method)) {
            call_user_func_array([$this, $this->method], $this->params);
        }
    }

    /**
     * 删除用户
     *
     * @param int $fd
     * @param string $needClose
     */
    protected function delUser($fd, $needClose = '1')
    {
        $server = app('swoole');
        // 关闭
        if ($needClose === '1' && $server->exist($fd)) {
            try {
                $server->close($fd);
            } catch (Throwable $e) {
                Log::channel('ws')->error('close error', ['fd' => $fd]);
            }
        } else {
            // 删除用户
            $userList = Util::userList();
            if (isset($userList[$fd])) {
                $need_broadcast = $userList[$fd]['user_type'] == User::TYPE_MEMBER;
                unset($userList[$fd]);
                Cache::forever(Util::CACHE_KEY_USER, $userList ?? []);

                $need_broadcast && $this->topdata();
            }
        }
    }

    /**
     * 广播
     *
     * @param $action
     * @param $data
     * @param int $user_type
     */
    private function broadcast($action, $user_type, Closure $data)
    {
        $fds = array_keys(Util::userList($user_type));
        if (!$fds) {
            return;
        }

        $data = json_encode(array_merge($data(), ['action' => $action]));

        foreach ($fds as $fd) {
            try {
                app('swoole')->push($fd, $data);
            } catch (Throwable $e) {
                $this->delUser($fd);
                Log::channel('ws')->error('Broadcast failure', ['fd' => $fd, 'data' => func_get_args()]);
            }
        }
    }

    private function send($action, int $user_id, Closure $data)
    {
        $fd = Util::userFd($user_id);

        if (!$fd) {
            return;
        }

        $data = json_encode(array_merge($data(), ['action' => $action]));

        try {
            app('swoole')->push($fd, $data);
        } catch (Throwable $e) {
            $this->delUser($fd);
            Log::channel('ws')->error('Send failure', ['fd' => $fd, 'data' => func_get_args()]);
        }
    }

    /**
     * 前台汇率数据广播
     */
    protected function forexdata()
    {
        $this->broadcast('forexdata', User::TYPE_MEMBER, function () {
            return [
                'forex' => Util::forex(),
                'now' => Carbon::now()->toDateTimeString(),
                'scenes' => Scene::all(),
            ];
        });
    }

    /**
     * 后台顶部统计广播
     */
    protected function topdata()
    {
        $this->broadcast('topdata', User::TYPE_ADMIN, function () {
            return Util::getTopData();
        });
    }

    /**
     * 会员聊天提醒
     * @param int $member_id
     */
    protected function memberChat(int $member_id)
    {
        $chats = Chat::where('type', Chat::TYPE_ADMIN)
            ->where('member_id', $member_id)
            ->where('is_read', false)
            ->get();
        if ($chats->isEmpty()) {
            return;
        }

        $this->send('newchat', $member_id, function () use ($chats) {
            return ['chats' =>$chats];
        });
    }

    /**
     * 管理员聊天提醒
     */
    protected function adminChat()
    {
        $chats = Chat::where('type', Chat::TYPE_MEMBER)
            ->where('is_read', false)
            ->get();
        if ($chats->isEmpty()) {
            return;
        }

        $this->broadcast('newchat', User::TYPE_ADMIN, function () use ($chats) {
            return ['chats' => $chats];
        });
    }

    /**
     * 检查是否登录
     *
     * @param int $fd
     */
    protected function checkLogin(int $fd)
    {
        if (app('swoole')->exist($fd)) {
            $userList = Util::userList();
            if (!isset($userList[$fd])) {
                $this->delUser($fd);
                Log::channel('ws')->error('User login failed', ['fd' => $fd]);
            }
        }
    }

    /**
     * 登录
     *
     * @param int $fd
     * @param string $token
     * @param int $type 汇率类型
     */
    protected function login(int $fd, string $token, int $type)
    {
        // 验证登录参数
        if (!$token) {
            $this->delUser($fd);
            Log::channel('ws')->error('Parameter error', ['params' => func_get_args()]);
            return;
        }

        // 验证token，识别用户
        /** @var JWTAuth $auth */
        $auth = app('tymon.jwt.auth');
        try {
            /** @var User $user */
            $user = $auth->setToken($token)->toUser();
            if (!$user) {
                throw new Exception('');
            }
        } catch (Exception $e) {
            $this->delUser($fd);
            Log::channel('ws')->error('User authorization failed', ['params' => func_get_args()]);
            return;
        }

        if ($user->type == User::TYPE_MEMBER) {
            if (!key_exists($type, Util::TYPE_INFO)) {
                // 如果是会员，则验证汇率类型
                $this->delUser($fd);
                Log::channel('ws')->error('Parameter error', ['params' => func_get_args()]);
                return;
            }
        } elseif ($user->type == User::TYPE_ADMIN) {
            // 如果是管理员，推送一条top数据
            $data = json_encode(array_merge(Util::getTopData(), ['action' => 'topdata']));
            try {
                app('swoole')->push($fd, $data);
            } catch (Throwable $e) {
                $this->delUser($fd);
                Log::channel('ws')->error('Broadcast failure', ['fd' => $fd, 'data' => $data]);
            }
        }

        // 保存用户信息
        $userList = Util::userList();
        if (!isset($userList[$fd])) {
            $userList[$fd] = ['id' => $user->id, 'type' => $type, 'user_type' => $user->type];
            Cache::forever(Util::CACHE_KEY_USER, $userList);
            $user->type == User::TYPE_MEMBER && $this->topdata();
        }

        // 查询新消息状态
        if ($user->isMember()) {
            $this->memberChat($user->id);
        }
        if ($user->isAdmin()) {
            $this->adminChat();
        }
    }
}
