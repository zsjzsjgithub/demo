<?php
/**
 * 登录记录
 */

namespace App;

use App\Models\Model;
use Carbon\Carbon;

/**
 * @property Carbon $time
 * @property User $user
 * @property bool $success
 */
class LoginLog extends Model
{
    public $timestamps = false;

    protected $hidden = ['user_id'];

    protected $dates = ['time'];

    protected $casts = ['success' => 'bool'];

    public static function bootSoftDeletes()
    {
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
