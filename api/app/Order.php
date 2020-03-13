<?php
/**
 * 订单
 *
 * -
 */

namespace App;

use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property Collection|OrderDetail[] $details
 * @property User $member
 * @property int $type
 * @property int $status
 * @property string $type_label
 * @property string $status_label
 * @property float $open
 * @property Carbon $scene_time
 * @property ForexData $forex_data
 * @property float $amount
 * @property float $rate
 * @property bool $is_notified
 * @property string $sn
 */
class Order extends Model
{
    /** 类型：买涨 */
    const TYPE_BUY = 1;

    /** 类型：买跌 */
    const TYPE_SELL = 2;

    /** 类型：和局 */
    const TYPE_CANCEL = 3;

    /** 状态：进行中 */
    const STATUS_PENDING = 1;

    /** 状态：赢得 */
    const STATUS_WIN = 2;

    /** 状态：失去 */
    const STATUS_LOSE = 3;

    protected $hidden = ['deleted_at', 'member_id', 'forex_data_id'];

    protected $casts = [
        'type' => 'int',
        'status' => 'int',
        'open' => 'float',
        'amount' => 'float',
        'rate' => 'float',
        'is_notified' => 'bool'
    ];

    protected $dates = ['deleted_at', 'scene_time'];

    protected $appends = ['type_label', 'status_label'];

    public function member()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getTypeLabelAttribute()
    {
        return __('message.order.type.' . $this->attributes['type']);
    }

    public function getStatusLabelAttribute()
    {
        return __('message.order.status.' . $this->attributes['status']);
    }

    public function forex_data()
    {
        return $this->belongsTo(ForexData::class);
    }
}
