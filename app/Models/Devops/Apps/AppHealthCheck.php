<?php

namespace App\Models\Devops\Apps;

use App\Models\Devops\BaseModel;

class AppHealthCheck extends BaseModel
{

    protected $table = 'app_health_check';
    protected $guarded = [];

    # 状态
    const STATUS_UNKNOWN  = 0; //未知
    const STATUS_NORMAL   = 1; //正常
    const STATUS_ERROR    = 2; //异常
    const STATUS_HANDLED  = 3; //已处理
    const STATUS_IGNORE   = 4; //已忽略
    const STATUS_MAP = [
        self::STATUS_UNKNOWN    => '未知',
        self::STATUS_NORMAL     => '正常',
        self::STATUS_ERROR      => '异常',
        self::STATUS_HANDLED    => '已处理',
        self::STATUS_IGNORE     => '已忽略',
    ];

}
