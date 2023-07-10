<?php

namespace App\Code;

class RedisCode
{

    /**
     * 样例
     * @param string $str
     * @return string
     */
    public static function smsCode($str = '')
    {
        return "demo:{$str}";
    }



}
