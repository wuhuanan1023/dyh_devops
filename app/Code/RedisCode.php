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

    /**
     * USER-SERV
     * @param int $user_id
     * @return string
     */
    public static function wsImUserServ($user_id)
    {
        return "swoole:im:user-serv:{$user_id}";
    }

    /**
     * SERV-USER
     * @param string $server_id
     * @return string
     */
    public static function wsImServUser($server_id)
    {
        return "swoole:im:serv-user:{$server_id}";
    }



}
