<?php

namespace App\Http\Middleware;

use App\Code\ResponseCode;
use App\Models\Devops\Apps\Apps;
use App\Traits\ApiResponseTrait;
use Closure;


class AppCheckSignMiddleware
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @param null $guard  //守护者 app_check_sign:app   app_check_sign:admin
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $app_key = $request->post('app_key');
        $sign    = $request->post('sign');
        $time    = $request->post('time');
        if (!Apps::checkSign($sign, $app_key, $time)) {
            return $this->failed('签名错误！', ResponseCode::SIGN_ERROR);
        }
        return $next($request);
    }

}
