<?php
/**
 * 代理伙伴
 */

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $data = User::withCount([
            'members',
            'members as deposit_sum' => function (Builder $builder) {
                $builder->select(DB::raw('SUM(deposit) as depositsum'));
            },
            'members as withdrawal_sum' => function (Builder $builder) {
                $builder->select(DB::raw('SUM(withdrawal) as withdrawalsum'));
            },
            'members as balance_sum' => function (Builder $builder) {
                $builder->select(DB::raw('SUM(balance) as balancesum'));
            },
        ])
            ->where(function (Builder $builder) use ($request) {
                $builder->where('type', User::TYPE_AGENT);
                if ($request->filled('name')) {
                    $builder->where(function (Builder $builder) use ($request) {
                        $builder->orWhere('username', 'like', '%' . $request->input('name') . '%')
                            ->orWhere('nickname', 'like', '%' . $request->input('name') . '%');
                    });
                }

                if ($request->filled('date_start')) {
                    $builder->where('logged_at', '>=', Carbon::parse($request->input('date_start'))->startOfDay());
                }

                if ($request->filled('date_end')) {
                    $builder->where('logged_at', '<=', Carbon::parse($request->input('date_end'))->endOfDay());
                }
            })
            ->latest()
            ->paginate($request->input('per_page'));

        return $data;
    }

    public function show(int $id)
    {
        return User::where('type', User::TYPE_AGENT)
            ->with([
                'logs' => function (HasMany $builder) {
                    $builder->limit(5)
                        ->where('success', true)
                        ->orderByDesc('time');
                },
            ])
            ->withCount([
                'members',
                'members as deposit_sum' => function (Builder $builder) {
                    $builder->select(DB::raw('SUM(deposit) as depositsum'));
                },
                'members as withdrawal_sum' => function (Builder $builder) {
                    $builder->select(DB::raw('SUM(withdrawal) as withdrawalsum'));
                },
                'members as balance_sum' => function (Builder $builder) {
                    $builder->select(DB::raw('SUM(balance) as balancesum'));
                },
            ])
            ->findOrFail($id);
    }

    /**
     * @param Request $request
     *
     * @throws ValidationException
     */
    public function create(Request $request)
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
            'password' => 'required|min:6',
            'commission_rate' => 'numeric',
        ]);

        $member = new User($request->only(['username', 'nickname', 'password', 'tel', 'commission_rate']));
        $member->type = User::TYPE_AGENT;
        $member->password = $request->input('password');
        $member->save();
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @throws ValidationException
     */
    public function update(Request $request, int $id)
    {
        $this->verify($request, [
            'username' => [
                'required',
                'unique:users,username,' . $id . ',id,deleted_at,NULL',
                'max:20',
                'min:5',
                'regex:/^[a-z][a-z_0-9]+$/',
            ],
            'nickname' => 'required|max:100',
            'password' => 'min:6',
        ]);

        $member = User::where('type', User::TYPE_AGENT)->findOrFail($id);
        // 重置密码
        if ($request->filled('password')) {
            $member->password = $request->input('password');
        }
        $member->update($request->only(['username', 'nickname', 'tel', 'commission_rate']));
    }

    public function toggleEnable(Request $request, int $id)
    {
        /** @var User $member */
        $member = User::where('type', User::TYPE_AGENT)->findOrFail($id);
        $member->is_enabled = $request->input('is_enabled', false);
        $member->save();
    }

    public function delete(Request $request)
    {
        $ids = $request->input('ids', []);
        if ($ids) {
            DB::transaction(function () use ($request, $ids) {
                User::where('type', User::TYPE_AGENT)->whereIn('id', $ids)->delete();
                User::where('type', User::TYPE_MEMBER)->whereIn('agent_id', $ids)->delete();
            });
        }
    }

}
