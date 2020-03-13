<?php
/**
 * 设置表
 */

namespace App;

use App\Models\Model;

/**
 * @property array $values
 */
class Setting extends Model
{
    public $timestamps = false;

    protected $casts = [
        'values' => 'array',
    ];

    protected $guarded = ['*'];

    protected $fillable = ['id', 'values'];

    public static function bootSoftDeletes()
    {
    }
}
