<?php

namespace App\Traits\WebSocket;

use App\Models\Devops\Apps\Apps;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait WebSocketCheck
{
    /**
     * 验证token，获取用户ID
     * @param string $token
     * @return int
     * @throws Exception
     */
    public function check(string $token)
    {
        try {
            if ( empty( $token ) ) {
                throw new Exception('no token');
            }

            Auth::setToken($token);

            $user_id = Auth::getPayload()->get('sub');

            if ( empty( $user_id ) ) {
                Log::info('Authorization fail', [$token]);

                throw new Exception('Authorization fail');
            }

            return $user_id;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * 验证 app_key，获取appID
     * @param string $app_key
     * @return int
     * @throws Exception
     */
    public function checkApp(string $app_key)
    {
        try {
            if (empty($app_key)) {
                throw new Exception('no app_key');
            }

            $app = Apps::query()->where('app_key', $app_key)->first();

            if (!$app) {
                Log::info('Authorization fail', [$app_key]);
                throw new Exception('Authorization fail');
            }
            return $app->id;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
