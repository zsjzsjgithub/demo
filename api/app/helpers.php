<?php
use App\Models\FhConfig;
use App\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 创建资源路由
 *
 * @param string $name 资源名(单数)
 */
function rest(string $name)
{
    $resource = str_plural($name);
    $pre = ucfirst(camel_case($name)) . 'Controller@';

    $router = app()->router;
    $router->get($resource, $pre . 'index');
    $router->get($resource . '/{id:\d+}', $pre . 'show');
    $router->post($resource, $pre . 'create');
    $router->put($resource . '/{id:\d+}', $pre . 'update');
    $router->delete($resource . '/{id:\d+}', $pre . 'delete');
}

/**
 * 添加表注释
 *
 * @param string $table_name 表名
 * @param string $comment 注释内容
 */
function table_comment(string $table_name, string $comment)
{
    DB::statement('ALTER TABLE `' . $table_name . '` comment "' . $comment . '"');
}

/**
 * 记录sql的开始点
 */
function sqlStart()
{
    /** @noinspection PhpUndefinedMethodInspection */
    DB::connection()->enableQueryLog();
}

/**
 * 获取从开始点记录的所有sql
 */
function sql()
{
    /** @noinspection PhpUndefinedMethodInspection */
    return DB::getQueryLog();
}

/**
 * 限制用户类型
 *
 * @param int[]|int|array $types
 *
 * @return User
 *
 * @throws AuthenticationException
 */
function allow($types)
{
    if (Auth::guest()) {
        throw new AuthenticationException(__('message.error.unauthenticated'));
    }

    /** @var User $user */
    $user = Auth::user();
    foreach (is_array($types) ? $types : func_get_args() as $type) {
        if ($user->type == $type) {
            return $user;
        }
    }

    throw new AuthenticationException(__('message.error.unauthenticated'));
}

function fhget(string $key = '', $default = null)
{
    return FhConfig::get($key, $default);
}