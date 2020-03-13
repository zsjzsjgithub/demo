<?php
/**
 * 账户记录
 *
 * -
 */

namespace App;

use App\Models\Model;

/**
 * @property User $user
 * @property int $type
 * @property string $type_label
 * @property int $status
 * @property string $status_label
 * @property User $member
 * @property float $balance
 * @property float $amount
 */
class AccountRecord extends Model
{
    /** 类型：存款 */
    const TYPE_DEPOSIT = 1;
    /** 类型：取款 */
    const TYPE_WITHDRAWAL = 2;
    /** 类型：购买 */
    const TYPE_BUY = 3;
    /** 类型：收入 */
    const TYPE_INCOME = 4;
    /** 类型：系统调整 */
    const TYPE_SYSTEM = 5;

    /** 状态：待审核 */
    const STATUS_PENDING = 1;
    /** 状态：已驳回 */
    const STATUS_DISMISSED = 2;
    /** 状态：已完成 */
    const STATUS_COMPLETED = 3;
    /** 状态：等待中 */
    const STATUS_WAITING = 4;

    protected $hidden = ['deleted_at', 'member_id'];

    protected $casts = [
        'type' => 'int',
        'status' => 'int',
        'amount' => 'float',
        'balance' => 'float',
    ];

    protected $appends = ['type_label', 'status_label'];

    public function member()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute()
    {
        return __('message.account.type.' . $this->type);
    }

    public function getStatusLabelAttribute()
    {
        return __('message.account.status.' . $this->status);
    }
}
