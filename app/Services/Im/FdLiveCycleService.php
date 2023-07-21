<?php

namespace App\Services\Im;

use Swoole\WebSocket\Server;
use Illuminate\Support\Facades\Redis;
use App\Code\RedisCode;

/**
 * Fd 生命周期类
 */
class FdLiveCycleService
{
    /**@var Redis $redis */
    private $redis;

    private $serv_id;

    public function __construct( $serv_id )
    {
        $this->serv_id = $serv_id;
        $this->redis = Redis::connection('default_persistent');
    }

    /**
     * UserId与FD绑定
     * @param $user_id
     * @param $fd
     */
    public function setFd($user_id,$fd)
    {
//        app('swoole')->wsTable->set('uid:' . $user_id, ['value' => $fd]);// 绑定uid到fd的映射
//        app('swoole')->wsTable->set('fd:' . $fd, ['value' => $user_id]);// 绑定fd到uid的映射
//
//        //redis中心注册
//        try {
//            $this->redis->set( RedisCode::wsImUserServ($user_id), $this->serv_id );
//            $this->redis->sAdd( RedisCode::wsImServUser($this->serv_id), $user_id );
//        } catch(\Exception $e) {
//            echo $e->getMessage();
//        }
    }



    /**
     * 关闭绑定
     * @param mixed $fd 要释放的FD
     */
    public function delFd($fd)
    {
        $fd_row = app('swoole')->wsTable->get('fd:' . $fd);

        if ($fd_row !== false && !empty( $fd_row['value'] ) ) {
            $user_id = $fd_row['value'];

            //用户本机当前FD
            $curr_fd = '';
            $user_row = app('swoole')->wsTable->get('uid:' . $user_id);
            if ( $user_row !== false && !empty( $user_row['value'] ) ) {
                $curr_fd = $user_row['value'];
            }

            //redis中心的用户当前服务器
            $user_serv_id = '';
            try {
                $user_serv_id = $this->redis->get( RedisCode::wsImUserServ( $user_id ) );
            } catch (\Exception $e) {

            }

            if ( !empty($curr_fd) ) {
                if ( $curr_fd == $fd ) {
                    if ( !empty( $user_serv_id ) ) {
                        if ( $user_serv_id == $this->serv_id ) {//同机同连，完全释放
                            app('swoole')->wsTable->del('uid:' . $user_id);//解绑uid映射

                            try {
                                $this->redis->set( RedisCode::wsImUserServ( $user_id ), '' );
                                $this->redis->srem( RedisCode::wsImServUser($this->serv_id), $user_id );
                            } catch(\Exception $e) {

                            }
                        } else {//跨机重连，部分释放
                            app('swoole')->wsTable->del('uid:' . $user_id);//解绑uid映射

                            try {
                                $this->redis->srem( RedisCode::wsImServUser($this->serv_id), $user_id );
                            } catch(\Exception $e) {

                            }
                        }
                    } else {//【一般不会走这里，非正常逻辑，异常处理】
                        app('swoole')->wsTable->del('uid:' . $user_id);//解绑uid映射

                        try {
                            $this->redis->srem( RedisCode::wsImServUser($this->serv_id), $user_id );
                        } catch(\Exception $e) {

                        }
                    }
                } else {//同机重连
                    //什么都不用做...
                }
            } else {//【一般不会走这里，非正常逻辑，异常处理】
                try {
                    $this->redis->srem( RedisCode::wsImServUser($this->serv_id), $user_id );
                } catch(\Exception $e) {

                }
            }
        }

        app('swoole')->wsTable->del('fd:' . $fd);//解绑fd映射
    }

    /**
     * 获取Fd通过uid 并判断是不是有效的连接
     * @param $uid
     * @return string
     */
    public static function getFdToUid($uid)
    {
        $uid = app('swoole')->wsTable->get('uid:' . $uid);
        if ($uid !== false) {
            $fd = $uid['value'];
            // 连接是否为有效的WebSocket客户端连接
            if (app('swoole')->isEstablished($fd)) {
                return $fd;
            }
            return '';
        }
        return '';
    }
}
