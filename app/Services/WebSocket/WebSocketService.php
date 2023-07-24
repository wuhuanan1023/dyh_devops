<?php


namespace App\Services\WebSocket;


use App\Services\Im\FdLiveCycleService;
use App\Traits\WebSocket\WebSocketCheck;
use App\Traits\WebSocket\WebSocketFormat;
use Exception;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Illuminate\Support\Facades\Log;
use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;

/**
 * WebSocket 服务脚本 -- Laravel-S 的 websocket.handler
 * Class WebSocketService
 * @package App\Services\WebSocket
 */
class WebSocketService implements WebSocketHandlerInterface
{
    use WebSocketFormat, WebSocketCheck;

    private $server_id;

    protected $publicKey;

    //fd的生命周期
    protected $fdLiveCycle;

    protected $log;

    public function __construct()
    {
        $serv_id = 'default';
        $this->log = Log::channel('websocket');

        try {
            echo base_path('.server_id');
            if (is_file(base_path('.server_id'))) {
                $this->server_id = $serv_id = trim(file_get_contents(base_path('.server_id')));// 过滤空字符串
            }
        } catch (Exception $e) {

        }

        $this->fdLiveCycle = new FdLiveCycleService($serv_id);

    }

    /**
     * 被客户端连接
     * @param Server $server
     * @param Request $request
     * @throws Exception
     */
    public function onOpen(Server $server, Request $request)
    {
        $ip = isset($request->server['remote_addr']) ? $request->server['remote_addr'] : 'unknow ip';

        //用户token验证
        $app_key = '';
        if (isset($request->header['app_key'])) {
            $app_key = $request->header['app_key'];
        } elseif (isset($request->get['app_key'])) {
            //增加GET传参方便调试
            $app_key = $request->get['app_key'];
        }

        //通过 app_key
        $app_id = $this->checkApp($app_key);

        //绑定userId和Fd
        $this->fdLiveCycle->setFd($app_id, $request->fd);

        $this->log->info('New WebSocket connection', [$request->fd, $ip]);

        try {
            // 记录在线状态
            $server->push($request->fd, "Welcome to connect to DYH Devops WebSocket, #{$request->fd} server_id: {$this->server_id}");

        } catch (Exception $e) {
            $this->log->info('fd#' . $request->fd, [$e->getMessage()]);
            $server->close($request->fd);
        }
    }

    /**
     * 接收到客户端消息
     * @param Server $server
     * @param Frame $frame
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $this->log->info('Received message', [$frame->fd, $frame->data, $frame->opcode, $frame->finish]);

        $server->push($frame->fd, date('Y-m-d H:i:s'));
    }

    /**
     * 客户端退出
     * @param Server $server
     * @param $fd
     * @param $reactorId
     */
    public function onClose(Server $server, $fd, $reactorId)
    {
        $this->fdLiveCycle->delFd($fd);
    }


}
