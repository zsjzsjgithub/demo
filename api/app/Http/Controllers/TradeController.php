<?php
/**
 * 交易
 *
 * -
 */

namespace App\Http\Controllers;

use App\AccountRecord;
use App\ForexData;
use App\Models\Scene;
use App\Order;
use App\OrderDetail;
use App\Server\Util;
use App\User;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TradeController extends Controller
{
    /**
     * @return array
     * @throws AuthenticationException
     */
    public function index()
    {
        $account = allow(User::TYPE_MEMBER);

        $rates = ForexData::where('type', Util::TYPE_GBPAUD)
            ->where('time', '>', Carbon::now()->subMinute(50))
            ->orderByDesc('time')
            ->get();

        $orderData = $account->orders()
            ->where(function (Builder $builder) {
                $builder->orWhere('status', Order::STATUS_PENDING)
                    ->orWhere(function (Builder $builder) {
                        $builder->where('status', '<>', Order::STATUS_PENDING)
                            ->where('is_notified', false);
                    });
            })
            ->get();

        $notifyData = $orderData->filter(function (Order $order) {
            return $order->status != Order::STATUS_PENDING;
        });
        $notifies = [];
        $notifyData->map(function (Order $order) use (&$notifies) {
            $order->is_notified = true;
            $order->save();
            $notifies[] = [
                'time' => $order->scene_time,
                'sn' => $order->sn,
                'status' => $order->status,
                'amount' => round($order->amount * $order->rate, 2),
            ];
        });

        $orders = $orderData->filter(function (Order $order) {
            return $order->status == Order::STATUS_PENDING;
        });

        $details = OrderDetail::whereHas('order', function (Builder $builder) use ($account) {
            $builder->where('member_id', $account->id)
                ->whereIn('scene_time', Scene::all()->pluck('time'));
        })->get();
        $values = [];
        if ($details->isNotEmpty()) {
            $details->map(function (OrderDetail $detail) use (&$values) {
                $order = $detail->order;
                $values[$order->scene_time->timestamp][] = array_merge($detail->only('count', 'price'), ['type' => $order->type]);
            });
        }

        $config = [
            'rate' => fhget('trade.rate'),
            'prices' => fhget('trade.prices'),
        ];

        return compact('rates', 'orders', 'account', 'config', 'values', 'notifies');
    }

    /**
     * @param Request $request
     *
     * @throws AuthenticationException|ValidationException
     */
    public function create(Request $request)
    {
        allow(User::TYPE_MEMBER);

        $this->verify($request, [
            'scene_time' => 'required|date',
            'type' => 'required|integer|in:1,2,3',
            'list' => 'required|array',
            'list.*.price' => 'required|numeric|in:' . implode(',', fhget('trade.prices', [
                    5000,
                    20000,
                    50000,
                    100000,
                    300000,
                    500000,
                ])),
            'list.*.count' => 'required|integer',
        ]);

        // 验证场次
        $scene_time = Carbon::parse($request->input('scene_time'));
        $scenes = Scene::all();
        $first = $scenes->first(function ($item) use ($scene_time) {
            $time = Carbon::createFromTimestamp($item['timestamp']);
            return $time->equalTo($scene_time) && $item['status'] === Scene::STATUS_OPEN;
        });
        if (!$first) {
            $this->error(__('message.trade.scene_error'));
        }

        /** @var User $user */
        $user = Auth::user();

        // 验证是否购买同一场次
        if (Order::where('scene_time', $scene_time)
            ->where('member_id', $user->id)
            ->exists()) {
            $this->error(__('message.trade.repeat_error'));
        }

        DB::transaction(function () use ($request, $scene_time, $user) {
            // 处理数据，合并同样价格的详情数据
            $details = [];
            $amount = 0;
            foreach ($request->input('list') as $detail) {
                $amount += $detail['price'] * $detail['count'];
                if (!isset($details[$detail['price']])) {
                    $details[$detail['price']] = $detail;
                } else {
                    $details[$detail['price']]['count'] += $detail['count'];
                }
            }

            // 验证并扣除余额
            if ($user->balance < $amount) {
                $this->error(__('message.trade.balance_error'));
            }
            $user->balance -= $amount;
            $user->save();

            // 计算订单号
            $count = Order::withTrashed()->whereDate('created_at', Carbon::today())->count();
            $sn = Carbon::today()->format('Ymd') . str_pad($count + 1, 6, '0', STR_PAD_LEFT);

            // 创建订单
            $order = new Order([
                'scene_time' => $scene_time,
                'type' => $request->input('type'),
                'rate' => fhget('trade.rate.' . $request->input('type'), 1.95),
                'amount' => $amount,
                'sn' => $sn,
            ]);
            $order->member()->associate($user);
            $order->save();

            // 生成详情数据
            $order->details()->createMany($details);

            // 生成交易记录
            $record = new AccountRecord([
                'amount' => $amount,
                'type' => AccountRecord::TYPE_BUY,
                'balance' => $user->balance,
                'status' => AccountRecord::STATUS_COMPLETED,
            ]);
            $record->member()->associate($user);
            $record->save();
        });
    }
}
