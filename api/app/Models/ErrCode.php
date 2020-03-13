<?php
/**
 * Description
 *
 * -
 */

namespace App\Models;

class ErrCode
{
    /**
     * <b>错误：</b>token失效
     * <br>前端清空token相关信息并跳转登录页面
     */
    const TOKEN_FAILED = 401;

    /**
     * <b>错误：</b>没有权限
     */
    const PERMISSION_DENIED = 403;

    /**
     * <b>错误：</b>找不到资源
     */
    const NOT_FOUND = 404;

    /**
     * <b>错误：</b>请求字段验证失败
     * <br>前端读取后端返回的多条错误信息
     */
    const VERIFICATION_FAILED = 422;

    /**
     * <b>错误：</b>服务端错误
     */
    const SERVER_ERROR = 500;
}
