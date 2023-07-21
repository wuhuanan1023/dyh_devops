<?php

namespace App\Traits\WebSocket;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait WebSocketCheckToken
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
}
