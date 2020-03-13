<?php
/**
 * 弹窗广告
 */

namespace App;

use App\Models\Model;

/**
 * @property mixed $is_enable
 * @property int $width
 * @property int $height
 * @property int $x
 * @property int $y
 */
class Pop extends Model
{
    protected $casts = [
        'width' => 'int',
        'height' => 'int',
        'x' => 'int',
        'y' => 'int',
        'is_enable' => 'bool',
    ];
}
