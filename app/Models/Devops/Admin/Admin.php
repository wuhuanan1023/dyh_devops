<?php

namespace App\Models\Devops\Admin;

use App\Models\Devops\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends BaseModel implements AuthenticatableContract, AuthorizableContract ,JWTSubject
{
    use Authenticatable, Authorizable;

    protected $table = 'admin';

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

    #账户状态
    const STATUS_ON  = 0; // 正常
    const STATUS_OFF = 1; // 禁用
    const STATUS_MAP = [
        self::STATUS_ON     => '正常',
        self::STATUS_OFF    => '禁用',
    ];


    # 是否超级管理员
    const IS_SUPER_OFF  = 0;// 否
    const IS_SUPER_ON   = 1;// 是
    const IS_SUPER_MAP = [
        self::IS_SUPER_OFF   => '否',
        self::IS_SUPER_ON    => '是',
    ];


}
