<?php
/**
 * 验证管理员IP
 */

namespace App\Http\Middleware;

use App\Models\ErrCode;
use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckIps
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guest()) {
            /** @var User $user */
            $user = Auth::user();
            if ($user->isAdmin()) {
                $ips = fhget('admin_ips', []);
                if (!in_array($request->ip(), $ips)) {
                    throw new HttpException(ErrCode::TOKEN_FAILED, 'Not allowed');
                }
            }
        }

        return $next($request);
    }
}
