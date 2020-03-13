<?php
/**
 * 汇率数据
 *
 * -
 */

namespace App\Http\Controllers;

use App\ForexData;
use App\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ForexController extends Controller
{
    /**
     * @param Request $request
     *
     * @return LengthAwarePaginator
     * @throws AuthenticationException
     */
    public function index(Request $request)
    {
        allow(User::TYPE_MEMBER);

        return ForexData::orderByDesc('id')
            ->where(function (Builder $builder) use ($request) {
                if ($request->filled('time_start')) {
                    $builder->where('time', '>=', $request->input('time_start'));
                }

                if ($request->filled('time_end')) {
                    $builder->where('time', '<=', $request->input('time_end'));
                }
            })
            ->paginate($request->input('per_page'));
    }
}
