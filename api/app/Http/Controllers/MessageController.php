<?php
/**
 * 客服
 *
 * -
 */

namespace App\Http\Controllers;

use App\Message;
use App\Server\WsTask;
use App\User;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $builder = Message::with('author')
            ->where(function (Builder $builder) use ($request) {
                if ($request->filled('type')) {
                    $builder->where('type', $request->input('type'));
                }

                if ($request->filled('title')) {
                    $builder->where('title', 'like', '%' . $request->input('title') . '%');
                }

                if ($request->filled('date_start')) {
                    $builder->where('created_at', '>=', Carbon::parse($request->input('date_start'))->startOfDay());
                }

                if ($request->filled('date_end')) {
                    $builder->where('created_at', '<=', Carbon::parse($request->input('date_end'))->endOfDay());
                }

                if ($request->filled('has_question')) {
                    $builder->where('has_question', true);
                }
            })
            ->where('type', '<>', Message::TYPE_REPLY);

        // 排序
        if ($request->input('type', 0) == Message::TYPE_SERVICE) {
            /** @var User $user */
            $user = Auth::user();
            if ($user->type == User::TYPE_MEMBER) {
                $builder->where('author_id', $user->id)->orderByDesc('has_answer');
            } else {
                $builder->orderByDesc('has_question');
            }

            $builder->latest('replied_at');
        } else {
            $builder->latest();
        }

        return $builder->paginate();
    }

    public function show(int $id)
    {
        /** @var Message|QBuilder $message */
        $message = Message::with([
            'author',
            'replies' => function (HasMany $builder) {
                $builder->with('author')
                    ->where('type', Message::TYPE_REPLY)
                    ->oldest();
            },
        ])->findOrFail($id);

        // 更新浏览量
        $message->increment('pageviews');

        /** @var User $user */
        $user = Auth::user();
        if ($user->type == User::TYPE_MEMBER && $message->has_answer) {
            $message->has_answer = false;
            $message->save();
        }
        if ($user->type == User::TYPE_ADMIN && $message->has_question) {
            $message->has_question = false;
            $message->save();
            Task::deliver(new WsTask('topdata'));
        }

        return $message;
    }

    /**
     * @param Request $request
     *
     * @throws ValidationException|AuthenticationException
     */
    public function create(Request $request)
    {
        $user = allow(User::TYPE_MEMBER, User::TYPE_ADMIN);

        $this->verify($request, ['title' => 'required']);

        $message = new Message($request->only(['title', 'content', 'type']));
        $message->author()->associate($user);
        $message->replied_at = Carbon::now();
        if ($user->type == User::TYPE_MEMBER) {
            // 验证上次提问时间大于10秒
            if (Message::whereIn('type', [Message::TYPE_SERVICE, Message::TYPE_REPLY])
                ->where('author_id', $user->id)
                ->where('created_at', '>=', Carbon::now()->subSeconds(10))
                ->exists()) {
                $this->error(__('message.message.question_error'));
            }
            $message->type = Message::TYPE_SERVICE;
            $message->has_question = true;
            Task::deliver(new WsTask('topdata'));
        }

        $message->save();
    }

    /**
     * @param Request $request
     *
     * @param int $id
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function update(Request $request, int $id)
    {
        allow(User::TYPE_ADMIN);

        $this->verify($request, ['title' => 'required', 'type' => 'required']);

        $message = Message::where('type', $request->input('type'))->findOrFail($id);
        $message->update($request->only('title', 'content'));
    }

    /**
     * 回复
     *
     * @param Request $request
     * @param int $id
     *
     * @throws ValidationException|AuthenticationException
     */
    public function reply(Request $request, int $id)
    {
        $user = allow(User::TYPE_MEMBER, User::TYPE_ADMIN);

        $this->verify($request, ['content' => 'required']);

        /** @var Message $message */
        $message = Message::where('type', Message::TYPE_SERVICE)->findOrFail($id);

        $message->replied_at = Carbon::now();
        if ($user->type == User::TYPE_MEMBER) {
            $message->has_question = true;
            $message->is_solved = false;
            // 验证上次提问时间大于10秒
            if (Message::whereIn('type', [Message::TYPE_SERVICE, Message::TYPE_REPLY])
                ->where('author_id', $user->id)
                ->where('created_at', '>=', Carbon::now()->subSeconds(10))
                ->exists()) {
                $this->error(__('message.message.question_error'));
            }
            Task::deliver(new WsTask('topdata'));
        } else {
            $message->has_answer = true;
        }
        $message->save();

        $message->replies()->create([
            'type' => Message::TYPE_REPLY,
            'content' => $request->input('content'),
            'author_id' => $user->id,
        ]);
    }

    /**
     * 确认解决
     *
     * @param int $id
     *
     * @throws AuthenticationException
     */
    public function confirm(int $id)
    {
        allow(User::TYPE_MEMBER);

        /** @var Message $message */
        $message = Message::where('type', Message::TYPE_SERVICE)->findOrFail($id);
        $message->is_solved = true;
        $message->has_question = false;
        $message->save();
        Task::deliver(new WsTask('topdata'));
    }

    public function delete(Request $request)
    {
        Message::whereIn('id', $request->input('ids', []))->delete();
        Task::deliver(new WsTask('topdata'));
    }
}
