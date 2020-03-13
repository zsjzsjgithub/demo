<?php
/**
 * 外汇数据
 *
 * -
 */

namespace App;

use App\Models\Model;
use App\Server\Util;
use Carbon\Carbon;

/**
 * @property int $type
 * @property Carbon $time
 * @property string $open
 * @property string $close
 * @property string $high
 * @property string $low
 * @property string $type_label
 */
class ForexData extends Model
{
    protected $fillable = [
        'type',
        'time',
        'open',
        'close',
        'high',
        'low',
    ];

    protected $dates = ['time'];

    protected $casts = [
        'type' => 'int',
    ];

    protected $appends = ['type_label'];

    public $timestamps = false;

    protected $forceDeleting = true;

    public static function bootSoftDeletes()
    {
    }

    public function getTypeLabelAttribute()
    {
        return Util::TYPE_INFO[$this->attributes['type']]['label'] ?? '';
    }
}
