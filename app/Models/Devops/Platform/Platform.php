<?php

namespace App\Models\Devops\Platform;

use App\Models\Devops\BaseModel;

/**
 * 平台 - 如：大娱号、百度、京东、...
 * Class Platform
 * @package App\Models\Devops\Platform
 */
class Platform extends BaseModel
{


    protected $table = 'platform';
    protected $guarded = [];


    #状态：0-禁用;1-启用;
    const STATUS_ON  = 1;
    const STATUS_OFF = 0;
    const STATUS_MAP = [
        self::STATUS_ON  => '启用',
        self::STATUS_OFF => '禁用',
    ];
}
