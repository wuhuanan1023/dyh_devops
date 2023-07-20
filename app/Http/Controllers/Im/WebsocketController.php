<?php
namespace App\Http\Controllers\Im;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller;

class WebsocketController extends Controller
{
    /**
     * 全域广播用户
     * @param Request $request
     */
	public function broadcast( Request $request)
	{
		try {
		    $data  = $request->post('data','');
		    $salt  = $request->post('salt','');
		    $sign  = $request->post('sign','');

		    if ( empty( $data ) || empty( $salt ) || empty( $sign ) ) {
		        Log::channel('im_http')->error('存在空参数',[$data,$sign,$salt]);
		        return;
		    }

		    if ( ws_http_sign($request->post()) !== $sign ) {
		        Log::channel('im_http')->error('签名验证失败',[$data,$sign,$salt]);
		        return;
		    }

			foreach ( app('swoole')->connections as $fd ) {
				if (app('swoole')->isEstablished($fd)) {
					app('swoole')->push( $fd, $data );
				}
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}

		return;
	}

	/**
	 * 用户事件通知
	 * @param Request $request
	 */
    public function notify( Request $request )
    {
        try {
            $users = $request->post('users');
            $data  = $request->post('data');
            $salt  = $request->post('salt');
            $sign  = $request->post('sign');

            if ( empty( $users ) || empty( $data ) || empty( $salt ) || empty( $sign ) ) {
                Log::channel('im_http')->error('存在空参数',[$users,$data,$sign,$salt]);
                return;
            }

            if ( ws_http_sign($request->post()) !== $sign ) {
                Log::channel('im_http')->error('签名验证失败',[$users,$data,$sign,$salt]);
                return;
            }

            $users = explode(',', $users);
            foreach ( $users as $user_id ) {
                $fd = FdLiveCycleService::getFdToUid( $user_id );
                if ( $fd ) {
                    Log::channel('ds_push')->info("push client:", [
                        'user_id'=>$user_id,
                        'data'=>$data,
                    ]);
                    app('swoole')->push( $fd, $data );
                } else {
                    Log::channel('ds_push')->info("push user offline:".$user_id);
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage(). "\n". $e->getTraceAsString();
        }

        return 'success';
    }

    private function checkSign( $data, $salt, $sign )
    {
        return md5( md5($data).md5($salt).$salt ) == $sign ? true : false;
    }

    //获取内存表内容
    public function table()
    {
        try {
            $data = '';
            foreach (app('swoole')->wsTable as $key => $row) {
                $data .=$key.','.$row['value']."\r\n";
            }
            return $data;
        } catch( \Exception $e ) {
            echo $e->getMessage();
        }
    }

    //在线人数
    public function online()
    {
        $i = 0;
        foreach ( app('swoole')->connections as $fd ) {
            if (app('swoole')->isEstablished($fd)) {
               ++$i;
            }
        }
        return $i;
    }

    //测试
    public function test()
    {

    }
}
