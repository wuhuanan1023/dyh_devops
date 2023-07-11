<?php

namespace App\Models\Devops\Apps;

use App\Models\Devops\BaseModel;

class Apps extends BaseModel
{

    protected $table = 'apps';
    protected $guarded = [];


    #状态 1开启0关闭
    const APP_STATUS_ON  = 1;
    const APP_STATUS_OFF = 0;
    const APP_STATUS_MAP = [
        self::APP_STATUS_ON  => '开启',
        self::APP_STATUS_OFF => '关闭',
    ];

    /**
     * 默认的 app_secret
     */
    const DEFAULT_APP_SECRET = '6r2kfo4cdz1pjywe';


    /**
     * 生成 APP_KEY
     * @return string
     */
    public static function createAppKey(): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $app_key = substr(str_shuffle($characters), 0, 10); // 输出10位随机字母(大小写混合)
        if (!Apps::query()->where('app_key', $app_key)->exists()) {
            return $app_key;
        }
        return self::createAppKey();
    }

    /**
     * 生成 APP_SECRET
     * @param $is_default
     * @return string
     */
    public static function createAppSecret($is_default = false): string
    {
        $characters = '123456789abcdefghijklmnopqrstuvwxyz';

        if ($is_default) {
            return self::DEFAULT_APP_SECRET;
        }

        $app_secret = substr(str_shuffle($characters), 0, 16);// 输出16位随机小写字母

        //没有重复，直接返回
        if (!Apps::query()->where('app_secret', $app_secret)->exists()) {
            return $app_secret;
        }
        return self::createAppSecret($is_default);
    }


}
