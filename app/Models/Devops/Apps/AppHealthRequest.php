<?php

namespace App\Models\Devops\Apps;

use App\Models\Devops\BaseModel;

class AppHealthRequest extends BaseModel
{

    protected $table = 'app_health_request';
    protected $guarded = [];

    # 状态
    const STATUS_UNKNOWN  = 0; //未知
    const STATUS_NORMAL   = 1; //正常
    const STATUS_ERROR    = 2; //异常
    const STATUS_MAP = [
        self::STATUS_UNKNOWN    => '未知',
        self::STATUS_NORMAL     => '正常',
        self::STATUS_ERROR      => '异常',
    ];


    # 服务器请求状态码
    const SERVER_SUCCESS            = 20000; // 服务器正常
    const SERVER_ERROR              = 50000; //服务器 异常
    const SERVER_ERROR_DB           = 50001; //Mysql 异常
    const SERVER_ERROR_REDIS        = 50002; //redis 异常
    const SERVER_ERROR_RABBITMQ     = 50003; //rabbitMQ 异常
    const SERVER_ERROR_WS           = 50004; //websocket 异常
    const SERVER_ERROR_SOCKET_IO    = 50005; //Socket.io 异常
    const SERVER_ERROR_MEETING      = 50006; //会议 异常
    const SERVER_ERROR_FFMPEG       = 50007; //FFMPEG 异常
    const SERVER_ERROR_THIRD        = 50008; //请求第三方服务异常
    const SERVER_ERROR_SELF         = 50009; //请求自己别的API异常
    const SERVER_CODE_MAP = [
        self::SERVER_SUCCESS            => '正常',
        self::SERVER_ERROR              => '服务器异常',
        self::SERVER_ERROR_DB           => '数据库异常',
        self::SERVER_ERROR_REDIS        => 'redis异常',
        self::SERVER_ERROR_RABBITMQ     => 'rabbitMQ异常',
        self::SERVER_ERROR_WS           => 'websocket异常',
        self::SERVER_ERROR_SOCKET_IO    => 'Socket.io异常',
        self::SERVER_ERROR_MEETING      => '会议异常',
        self::SERVER_ERROR_FFMPEG       => 'FFMPEG异常',
        self::SERVER_ERROR_THIRD        => '请求第三方服务异常',
        self::SERVER_ERROR_SELF         => '请求自己其他API异常',
    ];


}
