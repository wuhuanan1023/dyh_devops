<?php

if (!function_exists('func_is_true')) {
    /**
     * @return bool
     */
    function func_is_true()
    {
        return true;
    }
}

/**
 * 签名验证
 * @param $params
 * @param string $salt
 * @return string
 */
function ws_http_sign($params, $salt = '')
{
    if (!isset($params['salt'])) {
        $params['salt'] = $salt;
    }
    $signPars = "";
    ksort($params);
    foreach ($params as $k => $v) {
        if ($v == '') continue;
        if ($k == 'sign') continue;
        $signPars .= $k . "=" . $v . "&";
    }
    return strtoupper(md5($signPars));
}

