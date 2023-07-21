<?php

namespace App\Services\Im;

use App\Traits\WsResponseDataFormat;
use Illuminate\Support\Facades\Log;
use Swoole\WebSocket\Server;

class PushService
{
    use WsResponseDataFormat;

    public function __construct()
    {

    }

    /**
     * @param $service
     * @param $fd
     * @param $data
     */
    public function pushFd(Server $service, $fd, $data)
    {
        Log::info('push WebSocket connection', [$data]);
        $service->push($fd, $data);
    }
}
