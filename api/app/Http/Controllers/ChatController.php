<?php
/**
 * 聊天消息
 */

namespace App\Http\Controllers;

use App\Chat;
use App\Server\WsTask;
use App\User;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $data = Chat::with('author', 'member')
            ->where(function (Builder $builder) use ($user, $request) {
                if ($user->isMember()) {
                    $member_id = $user->id;
                } else {
                    $member_id = $request->input('member_id', 0);
                }
                $builder->where('member_id', $member_id);
            })->latest()->paginate($request->input('per_page'));

        $data->each(function (Chat $chat) use ($user) {
            if (($user->isMember() && $chat->type === Chat::TYPE_ADMIN) || (!$user->isMember() && $chat->type === Chat::TYPE_MEMBER)) {
                $chat->update(['is_read' => true]);
            }
        });

        return $data;
    }

    public function create(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->isMember()) {
            $member_id = $user->id;
            $type = Chat::TYPE_MEMBER;

        } else {
            $member_id = $request->input('member_id', 0);
            $type = Chat::TYPE_ADMIN;
        }

        Chat::create([
            'author_id' => Auth::id(),
            'member_id' => $member_id,
            'content' => $request->input('content'),
            'type' => $type,
        ]);

        // 消息提示
        if ($type === Chat::TYPE_ADMIN) {
            Task::deliver(new WsTask('memberChat', $member_id));
        } else {
            Task::deliver(new WsTask('adminChat'));
        }

    }
}
