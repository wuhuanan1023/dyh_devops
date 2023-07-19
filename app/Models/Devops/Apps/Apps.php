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

        /*if ($is_default) {
            return self::DEFAULT_APP_SECRET;
        }*/

        $app_secret = substr(str_shuffle($characters), 0, 16);// 输出16位随机小写字母

        //没有重复，直接返回
        if (!Apps::query()->where('app_secret', $app_secret)->exists()) {
            return $app_secret;
        }
        return self::createAppSecret($is_default);
    }


    /**
     * 创建APP
     * @param $platform_id
     * @param $app_name
     * @param $app_key
     * @param $app_secret
     * @param string $remark
     * @param int $status
     * @return mixed
     */
    public static function createApp($platform_id, $app_name, $app_key, $app_secret, $remark = '', $status = Apps::APP_STATUS_ON)
    {
        //`platform_id` int(11) DEFAULT '0' COMMENT '所属平台ID',
        //`app_name` varchar(64) NOT NULL DEFAULT '' COMMENT '应用名称',
        //`app_key` varchar(64) DEFAULT '' COMMENT '应用标识',
        //`app_secret` varchar(100) DEFAULT '' COMMENT '应用秘钥',
        //`remark` varchar(255) DEFAULT '' COMMENT '备注说明',
        //`status` tinyint(4) DEFAULT '0' COMMENT '状态：1-启用； 0-禁用',
        //`created_ts` int(11) DEFAULT '0' COMMENT '创建时间戳',
        //`updated_ts` int(11) DEFAULT '0' COMMENT '更新时间戳',
        return Apps::query()->create([
            'platform_id' => $platform_id,
            'app_name'    => $app_name,
            'app_key'     => $app_key,
            'app_secret'  => $app_secret,
            'remark'      => $remark ?: '',
            'status'      => $status,
            'created_ts'  => time(),
            'updated_ts'  => time(),
        ]);
    }


}
