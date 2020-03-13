<?php
/**
 * 公共数据接口
 *
 * -
 */

namespace App\Http\Controllers;

use App\AccountRecord;
use App\LoginLog;
use App\Message;
use App\Models\ErrCode;
use App\Server\WsTask;
use App\User;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DataController
{
    /**
     * @return array
     * @throws AuthenticationException
     */
    public function agents()
    {
        allow(User::TYPE_ADMIN, User::TYPE_AGENT);

        return User::where('type', User::TYPE_AGENT)->get();
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws AuthenticationException
     */
    public function statistics(Request $request)
    {
        $user = allow(User::TYPE_ADMIN, User::TYPE_AGENT);

        if ($request->filled('date_start', 'date_end')) {
            $date = [
                Carbon::parse($request->input('date_start'))->startOfDay(),
                Carbon::parse($request->input('date_end'))->endOfDay(),
            ];
        }

        if ($user->isAgent()) {
            $agent_id = function (Builder $builder) use ($user) {
                $builder->where('id', $user->id);
            };
        } elseif ($request->filled('agent_id')) {
            $agent_id = function (Builder $builder) use ($request) {
                $builder->where('id', $request->input('agent_id'));
            };
        }

        // 按天算
        $data = [];
        $agent_ids = [];

        // 存款
        $builder_deposit = AccountRecord::with('member.agent')
            ->has('member.agent')
            ->where('type', AccountRecord::TYPE_DEPOSIT)
            ->where('status', AccountRecord::STATUS_COMPLETED)
            ->orderByDesc('updated_at');
        isset($date) && $builder_deposit->whereBetween('updated_at', $date);
        isset($agent_id) && $builder_deposit->whereHas('member.agent', $agent_id);
        $records_deposit = $builder_deposit->get();
        /** @var AccountRecord $record */
        foreach ($records_deposit as $record) {
            $day = $record->updated_at->toDateString();
            $agent = $record->member->agent;
            $agent_ids[] = $agent->id;

            if (!isset($data[$day][$agent->id]['deposit'])) {
                $data[$day][$agent->id]['deposit'] = 0;
            }
            $data[$day][$agent->id]['deposit'] += $record->amount;

            if (!isset($data[$day][$agent->id]['deposit_count'])) {
                $data[$day][$agent->id]['deposit_count'] = 0;
            }
            $data[$day][$agent->id]['deposit_count']++;
        }

        // 取款
        $builder_withdrawal = AccountRecord::with('member.agent')
            ->has('member.agent')
            ->where('type', AccountRecord::TYPE_WITHDRAWAL)
            ->where('status', AccountRecord::STATUS_COMPLETED)
            ->orderByDesc('updated_at');
        isset($date) && $builder_withdrawal->whereBetween('updated_at', $date);
        isset($agent_id) && $builder_withdrawal->whereHas('member.agent', $agent_id);
        $records_withdrawal = $builder_withdrawal->get();
        /** @var AccountRecord $record */
        foreach ($records_withdrawal as $record) {
            $day = $record->updated_at->toDateString();
            $agent = $record->member->agent;
            $agent_ids[] = $agent->id;

            if (!isset($data[$day][$agent->id]['withdrawal'])) {
                $data[$day][$agent->id]['withdrawal'] = 0;
            }
            $data[$day][$agent->id]['withdrawal'] += $record->amount;

            if (!isset($data[$day][$agent->id]['withdrawal_count'])) {
                $data[$day][$agent->id]['withdrawal_count'] = 0;
            }
            $data[$day][$agent->id]['withdrawal_count']++;
        }

        // 访客数量
        $builder_login = LoginLog::with('user.agent')
            ->whereHas('user', function (Builder $builder) {
                $builder->where('type', User::TYPE_MEMBER)->has('agent');
            })
            ->where('success', true)
            ->orderByDesc('time')
            ->groupBy('user_id');
        isset($date) && $builder_login->whereBetween('time', $date);
        isset($agent_id) && $builder_login->whereHas('user.agent', $agent_id);
        $records_login = $builder_login->get();
        /** @var LoginLog $record */
        foreach ($records_login as $record) {
            $day = $record->time->toDateString();
            $agent = $record->user->agent;
            $agent_ids[] = $agent->id;

            if (!isset($data[$day][$agent->id]['login'])) {
                $data[$day][$agent->id]['login'] = 0;
            }

            $data[$day][$agent->id]['login']++;
        }

        // 余额
        $agents = User::where('type', User::TYPE_AGENT)->findMany(array_unique($agent_ids));
        $agent_data = [];
        /** @var User $agent */
        foreach ($agents as $agent) {
            $agent_data[$agent->id] = $agent;
        }

        $default = [
            'date' => __('message.statistic.sum'),
            'agent' => null,
            'deposit' => 0,
            'deposit_count' => 0,
            'withdrawal' => 0,
            'withdrawal_count' => 0,
            'profit' => 0,
            'income' => 0,
            'login' => 0,
            'type' => 'sum',
        ];
        $result = [];
        $total_data = array_merge($default, [
            'date' => __('message.statistic.total'),
            'type' => 'total',
        ]);
        foreach ($data as $day => $agents) {
            $day_data = $default;
            foreach ($agents as $agent_id => $v) {
                if (!isset($agent_data[$agent_id])) {
                    continue;
                }
                $agent = $agent_data[$agent_id];
                $profit = ($v['deposit'] ?? 0) - ($v['withdrawal'] ?? 0);
                $income = $profit * $agent->commission_rate / 100;
                $result[] = [
                    'date' => $day,
                    'agent' => $agent->only('id', 'nickname', 'username', 'commission_rate'),
                    'deposit' => $v['deposit'] ?? 0,
                    'deposit_count' => $v['deposit_count'] ?? 0,
                    'withdrawal' => $v['withdrawal'] ?? 0,
                    'withdrawal_count' => $v['withdrawal_count'] ?? 0,
                    'profit' => $profit,
                    'income' => $income,
                    'login' => $v['login'] ?? 0,
                    'type' => 'data',
                ];
                $day_data['deposit'] += $v['deposit'] ?? 0;
                $day_data['deposit_count'] += $v['deposit_count'] ?? 0;
                $day_data['withdrawal'] += $v['withdrawal'] ?? 0;
                $day_data['withdrawal_count'] += $v['withdrawal_count'] ?? 0;
                $day_data['profit'] += $profit;
                $day_data['income'] += $income;
                $day_data['login'] += $v['login'] ?? 0;
                $total_data['deposit'] += $v['deposit'] ?? 0;
                $total_data['deposit_count'] += $v['deposit_count'] ?? 0;
                $total_data['withdrawal'] += $v['withdrawal'] ?? 0;
                $total_data['withdrawal_count'] += $v['withdrawal_count'] ?? 0;
                $total_data['profit'] += $profit;
                $total_data['income'] += $income;
                $total_data['login'] += $v['login'] ?? 0;
            }

            $result[] = $day_data;
        }

        array_unshift($result, $total_data);

        return $result;
    }

    /**
     * @param Request $request
     *
     * @throws AuthenticationException
     */
    public function clean(Request $request)
    {
        $admin = allow(User::TYPE_ADMIN);
        $password = $request->input('password');
        if (!Hash::check($password, $admin->password)) {
            throw new HttpException(ErrCode::SERVER_ERROR, __('message.data.password_error'));
        }

        switch ($request->input('type', 0)) {
            // 重置会员注册日期
            case 1:
                User::where('type', User::TYPE_MEMBER)->update(['created_at' => null]);
                break;

            // 删除交易订单
            case 2:
                DB::table('orders')->truncate();
                DB::table('order_details')->truncate();
                break;

            // 删除账户记录
            case 3:
                AccountRecord::whereIn('type', [AccountRecord::TYPE_BUY, AccountRecord::TYPE_INCOME])->forceDelete();
                break;

            // 删除财务记录
            case 4:
                AccountRecord::whereIn('type', [AccountRecord::TYPE_DEPOSIT, AccountRecord::TYPE_WITHDRAWAL])->forceDelete();
                break;

            // 删除1:1提问
            case 5:
                Message::whereIn('type', [Message::TYPE_SERVICE, Message::TYPE_REPLY])->forceDelete();
                break;

            default:
                throw new HttpException(ErrCode::SERVER_ERROR, '类型错误');
                break;
        }

        Task::deliver(new WsTask('topdata'));
    }
}
