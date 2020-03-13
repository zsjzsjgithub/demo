<?php
/**
 * 客户
 */

namespace App\Http\Controllers;

use App\Exports\MemberExport;
use App\Server\Util;
use App\Server\WsTask;
use App\User;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    /**
     * @param Request $request
     *
     * @return LengthAwarePaginator
     * @throws AuthenticationException
     */
    public function index(Request $request)
    {
        $user = allow(User::TYPE_AGENT, User::TYPE_ADMIN);

        $data = User::with('agent')
            ->select('*', DB::raw('deposit - withdrawal as profit'))
            ->where(function (Builder $builder) use ($request, $user) {
                if ($user->isAgent()) {
                    $builder->where('agent_id', $user->id);
                }
                $builder->where('type', User::TYPE_MEMBER);
                if ($request->filled('name')) {
                    $builder->where(function (Builder $builder) use ($request) {
                        $builder->orWhere('username', 'like', '%' . $request->input('name') . '%')
                            ->orWhere('nickname', 'like', '%' . $request->input('name') . '%');
                    });
                }

                if ($request->filled('agent_id')) {
                    $builder->where('agent_id', $request->input('agent_id'));
                }

                if ($request->filled('is_enabled')) {
                    $builder->where('is_enabled', $request->input('is_enabled') === 'true');
                }

                if ($request->filled('date_start')) {
                    $builder->where('created_at', '>=', Carbon::parse($request->input('date_start'))->startOfDay());
                }

                if ($request->filled('date_end')) {
                    $builder->where('created_at', '<=', Carbon::parse($request->input('date_end'))->endOfDay());
                }

                if ($request->filled('logged_date_start')) {
                    $builder->where('logged_at', '>=', Carbon::parse($request->input('logged_date_start'))->startOfDay());
                }

                if ($request->filled('logged_date_end')) {
                    $builder->where('logged_at', '<=', Carbon::parse($request->input('logged_date_end'))->endOfDay());
                }

                // 查看在线用户
                if ($request->input('online') && $request->input('online')) {
                    $user_list = Util::userList(User::TYPE_MEMBER);
                    $ids = array_unique(array_column($user_list, 'id'));
                    $builder->whereIn('id', $ids);
                }
            })
            ->orderBy($request->input('order_by', 'id'), $request->input('order_type', 'desc'))
            ->paginate($request->input('per_page'));

        /** @noinspection PhpUndefinedMethodInspection */
        $data->makeVisible('agent_id');
        return $data;
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws AuthenticationException
     */
    public function show(int $id)
    {
        allow(User::TYPE_AGENT, User::TYPE_ADMIN);
        return User::where('type', User::TYPE_MEMBER)
            ->with([
                'agent',
                'logs' => function (HasMany $builder) {
                    $builder->limit(5)
                        ->where('success', true)
                        ->orderByDesc('time');
                },
            ])
            ->findOrFail($id);
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function update(Request $request, int $id)
    {
        allow(User::TYPE_ADMIN);

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
            'tel' => 'required',
            'bank_name' => 'required',
            'bank_number' => 'required',
            'agent_id' => [
                'required',
                Rule::exists('users', 'id')->whereNull('deleted_at')->where('type', User::TYPE_AGENT),
            ],
        ]);

        $member = User::where('type', User::TYPE_MEMBER)->findOrFail($id);
        // 重置密码
        if ($request->filled('password')) {
            $member->password = $request->input('password');
        }
        $member->update($request->only(['username', 'nickname', 'tel', 'bank_name', 'bank_number', 'agent_id']));
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @throws AuthenticationException
     */
    public function toggleEnable(Request $request, int $id)
    {
        allow(User::TYPE_ADMIN);
        /** @var User $member */
        $member = User::where('type', User::TYPE_MEMBER)->findOrFail($id);
        $member->is_enabled = $request->input('is_enabled', false);
        $member->save();
    }

    /**
     * @param Request $request
     *
     * @throws AuthenticationException
     */
    public function delete(Request $request)
    {
        allow(User::TYPE_ADMIN);
        $ids = $request->input('ids', []);
        if ($ids) {
            User::where('type', User::TYPE_MEMBER)->whereIn('id', $ids)->delete();
            Task::deliver(new WsTask('topdata'));
        }
    }

    /**
     * @return mixed
     * @throws AuthenticationException
     */
    public function export()
    {
        allow(User::TYPE_ADMIN);
        return Excel::download(new MemberExport(), 'member_' . Carbon::now()->format('YmdHis') . '.xlsx');
    }

}
