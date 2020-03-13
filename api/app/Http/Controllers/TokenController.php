<?php
/**
 * 登录token
 *
 * -
 */

namespace App\Http\Controllers;

use App\Server\WsTask;
use App\User;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\JWTAuth;

class TokenController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * 验证用户名和代理人是否存在
     *
     * @param Request $request
     *
     * @return array
     * @throws ValidationException
     */
    public function check(Request $request)
    {
        $id = $request->input('id', 'NULL');
        $this->verify($request, [
            'username' => [
                'filled',
                'unique:users,username,' . $id . ',id,deleted_at,NULL',
                'max:20',
                'min:6',
                'regex:/^[a-z][a-z_0-9]+$/',
            ],
            'agent_name' => [
                'filled',
                Rule::exists('users', 'username')->whereNull('deleted_at')->whereIn('type', [User::TYPE_AGENT, User::TYPE_MEMBER]),
            ],
        ]);
        return ['check' => true];
    }

    /**
     * 注册
     *
     * @param Request $request
     *
     * @return array
     * @throws ValidationException
     */
    public function register(Request $request)
    {
        $this->verify($request, [
            'username' => [
                'required',
                'unique:users,username,NULL,id,deleted_at,NULL',
                'max:20',
                'min:5',
                'regex:/^[a-z][a-z_0-9]+$/',
            ],
            'nickname' => 'required|max:100',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required',
            'tel' => 'required',
            'bank_name' => 'required',
            'bank_number' => 'required',
            'agent_name' => [
                Rule::exists('users', 'username')->whereNull('deleted_at')->whereIn('type', [User::TYPE_AGENT, User::TYPE_MEMBER]),
            ],
            'agent_id' => [
                Rule::exists('users', 'id')->whereNull('deleted_at')->where('type', User::TYPE_AGENT),
            ],
        ]);

        if (!$request->filled('agent_id') && !$request->filled('agent_name')) {
            $this->verifyError('agent_name', __('message.token.agent_empty'));
        }

        // 是否是后台创建
        $is_admin = $request->filled('agent_id');

        $user = new User($request->only(['username', 'nickname', 'password', 'tel', 'bank_name', 'bank_number']));
        $user->type = User::TYPE_MEMBER;
        // 设置代理人
        if ($is_admin) {
            $agent = User::where('type', User::TYPE_AGENT)->find($request->input('agent_id'));
        } else {
            if (!fhget('allow_register')) {
                $this->error('不允许注册');
            }
            $user->logged_at = Carbon::now();
            $agent = User::where('username', $request->input('agent_name'))->whereIn('type', [User::TYPE_AGENT, User::TYPE_MEMBER])->first();
        }
        $user->agent()->associate($agent);
        $user->password = $request->input('password');
        $user->save();

        Task::deliver(new WsTask('topdata'));

        if ($is_admin) {
            return [];
        }

        $this->loginLog($user, $request);

        return $this->respondWithToken($this->jwt->fromUser($user), $user->type);
    }

    /**
     * 记录登录日志
     *
     * @param User $user
     * @param Request $request
     * @param bool $success
     */
    private function loginLog(User $user, Request $request, $success = true)
    {
        $user->logs()->create([
            'time' => Carbon::now(),
            'ip' => $request->ip(),
            'success' => $success,
        ]);
    }

    /**
     * 登录
     *
     * @param Request $request
     *
     * @return array
     */
    public function create(Request $request)
    {
        if (!$token = $this->jwt->attempt($request->only('username', 'password', 'type'))) {
            $this->error(__('message.token.login_error'));
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$request->filled('type')) {
            if (!in_array($user->type, [User::TYPE_ADMIN, User::TYPE_AGENT])) {
                $this->loginLog($user, $request, false);
                $this->error(__('message.error.unauthenticated'));
            }
        }

        if (!$user->is_enabled) {
            $this->loginLog($user, $request, false);
            $this->error(__('message.token.user_disabled'));
        }

        // 验证登录ip
        if ($user->isAdmin()) {
            $ips = fhget('admin_ips', []);
            if (!in_array($request->ip(), $ips)) {
                $this->loginLog($user, $request, false);
                $this->error('Not allowed');
            }
        }

        // 更新登录时间
        $user->logged_at = Carbon::now();
        $user->save();

        // 登录日志
        $this->loginLog($user, $request);

        return $this->respondWithToken($token, $user->type);
    }

    /**
     * 更新token
     *
     * @param Request $request
     *
     * @return array
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function update(Request $request)
    {
        if (!$this->jwt->parser()->setRequest($request)->hasToken()) {
            throw new UnauthorizedHttpException('jwt-auth', 'Token not provided');
        }

        return $this->respondWithToken($this->jwt->parseToken()->refresh(), 0);
    }

    /**
     * 退出
     *
     * @return string
     */
    public function delete()
    {
        $this->jwt->blacklist()->add($this->jwt->payload());

        return __('message.token.exited');
    }

    /**
     * 处理返回token格式
     *
     * @param  string $token
     *
     * @param $type
     *
     * @return array
     */
    protected function respondWithToken($token, $type)
    {
        $data = [
            'token' => $token,
            'type' => 'Bearer',
            'expired_at' => time() + $this->jwt->factory()->getTTL() * 60,
        ];

        if ($type) {
            $data['user_type'] = $type;
        }

        return $data;
    }

    /**
     * 修改个人密码
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|string
     * @throws ValidationException
     */
    public function password(Request $request)
    {
        $this->verify($request, [
            'old_password' => 'required',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required',
        ]);
        // 验证旧密码
        /** @var User $user */
        $user = Auth::user();
        if (!Hash::check($request->input('old_password'), $user->password)) {
            $this->verifyError('old_password', __('message.token.old_password_error'));
        }
        $user->password = $request->input('password');
        $user->save();
        return 'ok';
    }

    /**
     * 修改个人资料
     *
     * @param Request $request
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function profile(Request $request)
    {
        $admin = allow(User::TYPE_ADMIN);

        $this->verify($request, [
            'username' => [
                'required',
                'unique:users,username,' . $admin->id . ',id,deleted_at,NULL',
                'max:20',
                'regex:/^[a-z][a-z_0-9]+$/',
            ],
            'nickname' => 'required|max:100',
        ]);
        $admin->update($request->only(['username', 'nickname']));
    }

    public function show()
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->only('id', 'username', 'nickname', 'balance', 'type');
    }

    public function allow()
    {
        return ['allow_register' => fhget('allow_register')];
    }

}
