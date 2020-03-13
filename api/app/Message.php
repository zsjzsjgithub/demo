<?php
/**
 * 信息
 *
 * -
 */

namespace App;

use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property string $type_label
 * @property User $author
 * @property Collection|Message[] $replies
 * @property int $type
 * @property Carbon $replied_at
 * @property bool $has_question
 * @property bool $has_answer
 * @property bool $is_solved
 * @property int $pageviews
 */
class Message extends Model
{
    /** 类型：1对1客服 */
    const TYPE_SERVICE = 1;

    /** 类型：常见问题 */
    const TYPE_PROBLEM = 2;

    /** 类型：通知 */
    const TYPE_NOTICE = 3;

    /** 类型：回复 */
    const TYPE_REPLY = 4;

    protected $appends = ['type_label'];

    protected $dates = ['deleted_at', 'replied_at'];

    protected $hidden = ['deleted_at', 'author_id', 'message_id'];

    protected $casts = [
        'has_question' => 'boolean',
        'has_answer' => 'boolean',
        'is_solved' => 'boolean',
        'type' => 'int',
        'pageviews' => 'int',
    ];

    public function getTypeLabelAttribute()
    {
        return __('message.message.type.' . $this->attributes['type']);
    }

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(Message::class);
    }
}
