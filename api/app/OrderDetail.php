<?php
/**
 * 订单明细
 *
 * -
 */

namespace App;

use App\Models\Model;

/**
 * @property Order $order
 * @property float $price
 * @property int $count
 */
class OrderDetail extends Model
{
    protected $hidden = ['deleted_at', 'order_id'];

    protected $casts = [
        'price' => 'float',
        'count' => 'int',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
