<?php

namespace App\Traits;

use App\Code\ResponseCode;

trait ApiResponse
{

    /**
     * @param array $data 数据
     * @param string $message 提示
     * @param int $code 状态码 1 - 成功，0 - 失败
     * @return mixed
     */
    public function success($data = [],$message = 'Success',$code = ResponseCode::API_SUCCESS){
        $response = [
            'code'  => $code,
            'msg'   => __($message),
            'data'  => $data,
        ];
        return response()->json($response);
    }

    /**
     * @param string $message 提示
     * @param int $code 状态码 1 - 成功，0 - 失败
     * @param array $data 相关参数
     * @return mixed
     */
    public function failed($message = 'Failed' ,$code = ResponseCode::API_FAILED , $data = []){
        $response = [
            'code'  => $code,
            'msg'   => __($message),
            'data'  => $data,
        ];
        return response()->json($response);
    }

}
