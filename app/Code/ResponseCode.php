<?php

namespace App\Code;

class ResponseCode
{

    //接口状态
    const API_SUCCESS = 20000;//成功
    const API_FAILED  = 40000;//失败

    const TOKEN_ERROR = 40001;//token错误

    const SIGN_ERROR  = 40002;//签名错误

}
