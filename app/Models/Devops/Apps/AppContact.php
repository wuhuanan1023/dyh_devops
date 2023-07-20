<?php

namespace App\Models\Devops\Apps;

use App\Models\Devops\BaseModel;

class AppContact extends BaseModel
{

    protected $table = 'app_contact';
    protected $guarded = [];

    # 联系人状态：0-禁用；1-正常
    const STATUS_OFF  = 0; //禁用
    const STATUS_ON   = 1; //正常
    const STATUS_MAP = [
        self::STATUS_OFF    => '禁用',
        self::STATUS_ON     => '正常',
    ];

}
