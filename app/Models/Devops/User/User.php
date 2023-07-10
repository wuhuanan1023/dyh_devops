<?php

namespace App\Models\Devops\User;

use App\Models\Devops\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract ,JWTSubject
{
    use Authenticatable, Authorizable;


    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    #状态
    const USER_BAN_OFF  = 0; //正常
    const USER_BAN_ON   = 1; //封禁
    const USER_BAN_MAP = [
        self::USER_BAN_OFF  => '正常',
        self::USER_BAN_ON   => '封禁',
    ];

    #默认头像
    const DEFAULT_AVATAR = 'avatar/20231978413.png';


}
