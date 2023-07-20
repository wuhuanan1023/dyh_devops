<?php

namespace App\Models\Devops\Apps;

use App\Models\Devops\BaseModel;

class AppWarning extends BaseModel
{

    protected $table = 'app_warning';
    protected $guarded = [];

//`app_id` int(11) DEFAULT '0' COMMENT '应用ID',
//`level` int(8) DEFAULT '0' COMMENT '等级：1001-Info；2001-Waring；3001-Error',
//`content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '日志内容',
//`request_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '请求IP',
//`status` tinyint(4) DEFAULT '0' COMMENT '状态：0-待处理；1-已处理；2-已忽略',
//`created_ts` int(11) DEFAULT '0' COMMENT '创建时间戳',
//`updated_ts` int(11) DEFAULT '0' COMMENT '更新时间戳',

    # 状态：0-待处理；1-已处理；2-已忽略
    const STATUS_WAIT   = 0; //待处理
    const STATUS_FINISH = 1; //已处理
    const STATUS_IGNORE = 2; //已忽略
    const STATUS_MAP = [
        self::STATUS_WAIT       => '待处理',
        self::STATUS_FINISH     => '已处理',
        self::STATUS_IGNORE     => '已忽略',
    ];

    #等级：1001-Info；2001-Warn；3001-Error
    const LEVEL_INFO    = 1001;
    const LEVEL_WARN    = 2001;
    const LEVEL_ERROR   = 3001;
    const LEVEL_MAP = [
        self::LEVEL_INFO    => '普通',
        self::LEVEL_WARN    => '警告',
        self::LEVEL_ERROR   => '致命',
    ];

}
