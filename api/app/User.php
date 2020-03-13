<?php

namespace App;

use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string username
 * @property string nickname
 * @property int $type 用户类型
 * @property string $password
 * @property bool $is_enabled 是否启用
 * @property User|null $agent
 * @property float $balance 余额
 * @property Collection|Order[] $orders
 * @property Collection|AccountRecord[] $records
 * @property Carbon $logged_at
 * @property float $deposit 存款
 * @property float $withdrawal 取款
 * @property Collection|LoginLog[] $logs
 * @property Collection|User[] $members
 * @property int $commission_rate
 * @property string $bank_name
 * @property string $bank_number
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    /** 类型：管理员 */
    const TYPE_ADMIN = 1;

    /** 类型：代理 */
    const TYPE_AGENT = 2;

    /** 类型：会员 */
    const TYPE_MEMBER = 3;

    use Authenticatable, Authorizable, HasRoles;

    protected $hidden = ['password', 'deleted_at', 'agent_id'];

    protected $casts = [
        'is_enabled' => 'bool',
        'type' => 'int',
        'balance' => 'float',
        'deposit' => 'float',
        'withdrawal' => 'float',
    ];

    protected $dates = ['deleted_at', 'logged_at'];

    public function setPasswordAttribute(string $password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }

    public function isAgent(): bool
    {
        return $this->type === self::TYPE_AGENT;
    }

    public function isMember(): bool
    {
        return $this->type === self::TYPE_MEMBER;
    }

    /**
     * 会员归属的代理伙伴
     */
    public function agent()
    {
        return $this->belongsTo(self::class);
    }

    /**
     * 代理伙伴下属的所有会员
     */
    public function members()
    {
        return $this->hasMany(self::class, 'agent_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'member_id');
    }

    public function records()
    {
        return $this->hasMany(AccountRecord::class, 'member_id');
    }

    public function logs()
    {
        return $this->hasMany(LoginLog::class);
    }

}
