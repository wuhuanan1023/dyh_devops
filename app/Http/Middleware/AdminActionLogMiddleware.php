<?php

namespace App\Http\Middleware;

use App\Models\Devops\System\SysAdminLog;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdminActionLogMiddleware
{

    public function handle($request, Closure $next)
    {
        //后置中间件，控制器内业务逻辑执行完毕后再执行下面逻辑
        //捕获控制器内请求响应
        $response = $next($request);

        // 利用 try catch 避免记录日志失败导致本次请求不能正常响应
        try {

            if (!($response instanceof JsonResponse))
            {
                return $response;
            }
            //捕获控制器内请求响应返回的json数据
            //记录日志内容
            $admin = Auth::guard('admin')->user();

            if(!$admin){
                return $response;
            }
            //管理员ID
            $admin_id = $admin->id;
            //操作路由
            $request_url = $request->getRequestUri();
            //请求参数
            $request_data = $request->all();
            $request_data = $request_data ? json_encode($request_data) : '';
            //响应数据
            $response_data = json_encode($response->getData(true));
            //IP
            $request_ip = func_app_ip();
            SysAdminLog::query()->create([
                'admin_id' => $admin_id,
                'request_url' => $request_url,
                'request_ip' => $request_ip,
                'request_data' => $request_data,
                'response_data' => $response_data,
                'created_ts' => time(),
            ]);
        } catch (\Exception $exception) {
            return $response;
        }
        return $response;
    }

}
