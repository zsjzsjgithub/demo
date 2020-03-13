<?php
/**
 * 订单
 *
 * -
 */

namespace App\Http\Controllers;

use App\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $builder = Order::with('forex_data', 'member')
            ->where(function (Builder $builder) use ($request) {
                if ($request->filled('name')) {
                    $builder->whereHas('member', function (Builder $builder) use ($request) {
                        $builder->where(function (Builder $builder) use ($request) {
                            $builder->orWhere('username', 'like', '%' . $request->input('name') . '%')
                                ->orWhere('username', 'like', '%' . $request->input('name') . '%');
                        });
                    });
                }

                if ($request->filled('date_start')) {
                    $builder->where('created_at', '>=', Carbon::parse($request->input('date_start'))->startOfDay());
                }

                if ($request->filled('date_end')) {
                    $builder->where('created_at', '<=', Carbon::parse($request->input('date_end'))->endOfDay());
                }

                if ($request->filled('scene_date_start')) {
                    $builder->where('scene_time', '>=', Carbon::parse($request->input('scene_date_start'))->startOfDay());
                }

                if ($request->filled('scene_date_end')) {
                    $builder->where('scene_time', '<=', Carbon::parse($request->input('scene_date_end'))->endOfDay());
                }

                if ($request->filled('sn')) {
                    $builder->where('sn', 'like', '%' . $request->input('sn') . '%');
                }

                if ($request->filled('type')) {
                    $builder->where('type', $request->input('type'));
                }

                if ($request->filled('status')) {
                    $builder->where('status', $request->input('status'));
                }
            });

        /** @var User $user */
        $user = Auth::user();
        if ($user->isMember()) {
            $builder->where('member_id', $user->id);
        } elseif ($user->isAgent()) {
            $builder->whereHas('member', function (Builder $builder) use ($user) {
                $builder->where('agent_id', $user->id);
            });
        }

        return $builder->orderByDesc('id')
            ->paginate($request->input('per_page'));
    }
}
