<?php
/**
 * 聊天消息
 */

namespace App;

use App\Models\Model;

/**
 * @property bool $is_read
 * @property string $content
 * @property mixed $author
 * @property mixed $member
 * @property int $type
 */
class Chat extends Model
{
    /** 类型：管理员发送 */
    const TYPE_ADMIN = 1;

    /** 类型：会员发送 */
    const TYPE_MEMBER = 2;

    protected $casts = ['is_read' => 'bool', 'type' => 'int'];

    protected $hidden = ['author_id', 'member_id'];

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function member()
    {
        return $this->belongsTo(User::class);
    }
}
