<?php
/**
 * 账户
 *
 * -
 */

namespace App\Http\Controllers;

use App\AccountRecord;
use App\Server\WsTask;
use App\User;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * @param Request $request
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $builder = AccountRecord::with('member')
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
                    $builder->where('updated_at', '>=', Carbon::parse($request->input('date_start'))->startOfDay());
                }

                if ($request->filled('date_end')) {
                    $builder->where('updated_at', '<=', Carbon::parse($request->input('date_end'))->endOfDay());
                }

                if ($request->filled('created_date_start')) {
                    $builder->where('created_at', '>=', Carbon::parse($request->input('created_date_start'))->startOfDay());
                }

                if ($request->filled('created_date_end')) {
                    $builder->where('created_at', '<=', Carbon::parse($request->input('created_date_end'))->endOfDay());
                }

                if ($request->filled('type')) {
                    $builder->where('type', $request->input('type'));
                }

                if ($request->filled('status')) {
                    $builder->where('status', $request->input('status'));
                }
            });

        // 财务模块
        if ($request->filled('finance') && $request->filled('finance')) {
            $builder->whereIn('type', [AccountRecord::TYPE_DEPOSIT, AccountRecord::TYPE_WITHDRAWAL])->latest();
        } else {
            $builder->orderByDesc('updated_at');
        }

        /** @var User $user */
        $user = Auth::user();
        if ($user->isMember()) {
            $builder->where('member_id', $user->id);
        } elseif ($user->isAgent()) {
            $builder->whereHas('member', function (Builder $builder) use ($user) {
                $builder->where('agent_id', $user->id);
            });
        }

        return $builder->paginate($request->input('per_page'));
    }

    /**
     * 存款
     *
     * @param Request $request
     *
     * @return string
     * @throws AuthenticationException|ValidationException
     */
    public function deposit(Request $request)
    {
        $user = allow(User::TYPE_MEMBER);

        $this->verify($request, [
            'amount' => 'required|numeric|min:1000',
        ]);

        // 处理当日首充福利
        $amount = $request->input('amount');
        if ($user->records()
            ->where('status', AccountRecord::TYPE_DEPOSIT)
            ->whereDate('created_at', Carbon::today())
            ->doesntExist()) {
            $amount *= 1 + fhget('account.first_rate', 0.1);
        }

        $record = new AccountRecord([
            'amount' => $amount,
            'type' => AccountRecord::TYPE_DEPOSIT,
            'balance' => $user->balance,
            'status' => AccountRecord::STATUS_PENDING,
        ]);
        $record->member()->associate($user);
        $record->save();

        Task::deliver(new WsTask('topdata'));

        return 'ok';
    }

    /**
     * 取款
     *
     * @param Request $request
     *
     * @return string
     * @throws ValidationException|AuthenticationException
     */
    public function withdrawal(Request $request)
    {
        $user = allow(User::TYPE_MEMBER);

        $this->verify($request, [
            'amount' => 'required|numeric|min:1000',
        ]);

        $amount = $request->input('amount');
        if ($amount % 1000 != 0) {
            $this->verifyError('amount', __('message.account.amount_error'));
        }

        if ($amount > $user->balance) {
            $this->verifyError('amount', __('message.account.amount_balance'));
        }

        DB::transaction(function () use ($user, $amount) {
            // 冻结余额
            $user->balance -= $amount;
            $user->save();

            $record = new AccountRecord([
                'amount' => $amount,
                'type' => AccountRecord::TYPE_WITHDRAWAL,
                'balance' => $user->balance,
                'status' => AccountRecord::STATUS_PENDING,
            ]);
            $record->member()->associate($user);
            $record->save();
            Task::deliver(new WsTask('topdata'));
        });

        return 'ok';
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @throws ValidationException
     */
    public function patch(Request $request, int $id)
    {
        $this->verify($request, [
            'status' => 'numeric|in:2,3,4',
        ]);

        /** @var AccountRecord $record */
        $record = AccountRecord::whereIn('type', [AccountRecord::TYPE_DEPOSIT, AccountRecord::TYPE_WITHDRAWAL])->findOrFail($id);

        DB::transaction(function () use ($record, $request) {
            $member = $record->member;
            $amount = $record->amount;
            if ($request->input('status') == AccountRecord::STATUS_COMPLETED) {
                // 已完成时，处理申请
                if ($record->type === AccountRecord::TYPE_DEPOSIT) {
                    // 处理存款
                    $balance = $member->balance + $amount;
                    $member->deposit += $amount;
                    $record->balance = $balance;
                    $member->balance = $balance;
                } else {
                    // 处理取款
                    $member->withdrawal += $amount;
                }
                $member->save();
            } elseif ($request->input('status') == AccountRecord::STATUS_DISMISSED) {
                // 已驳回时，处理申请
                if ($record->type === AccountRecord::TYPE_WITHDRAWAL) {
                    // 处理取款
                    $balance = $member->balance + $amount;
                    $record->balance = $balance;
                    $member->balance = $balance;
                }
                $member->save();
            }

            $record->status = $request->input('status');
            $record->save();
        });
        Task::deliver(new WsTask('topdata'));
    }

    /**
     * 余额调整
     *
     * @param Request $request
     * @param int $id
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function balance(Request $request, int $id)
    {
        allow(User::TYPE_ADMIN);

        $this->verify($request, [
            'amount' => 'required|numeric',
        ]);

        $member = User::where('type', User::TYPE_MEMBER)->findOrFail($id);
        $amount = $request->input('amount');
        $balance = $member->balance + $amount;

        if ($balance < 0) {
            $this->verifyError('amount', __('message.account.amount_balance'));
        }

        DB::transaction(function () use ($member, $balance, $amount) {
            $member->records()->create([
                'amount' => $amount,
                'type' => AccountRecord::TYPE_SYSTEM,
                'balance' => $balance,
                'status' => AccountRecord::STATUS_COMPLETED,
            ]);
            $member->balance = $balance;
            $member->save();
        });
    }
}
