<?php

use App\Models\Manhe\User\User;
use Carbon\Carbon;
use GeoIp2\Database\Reader;
use Illuminate\Support\Str;

############################# 公用 ↓ ↓ ↓  ##############################
/**
 * 获取最终 sql
 * @param $query_str
 * @return string
 */
function func_get_sql($query_str)
{
    $query = str_replace(array('?'), array('\'%s\''), $query_str->toSql());
    $query = vsprintf($query, $query_str->getBindings());
    return $query;
}

/**
 * 转时间格式
 * @param int|string $datetime 时间戳或者时间格式
 * @param string $timezone 时区
 * @param string $format 格式
 * @return string
 */
function func_datetime_trans($datetime, string $timezone = DEFAULT_TIMEZONE, $format = 'Y-m-d H:i:s')
{
    if(empty($datetime)) return '';
    return is_numeric($datetime)
        ? Carbon::parse($datetime)->setTimezone($timezone)->format($format)
        : Carbon::parse($datetime,$timezone)->format($format);
}

/**
 * 时间格式转时间戳
 * @param string|int $datetime 日期
 * @param string $timezone 时区
 * @param string $type 默认-当前 | start-00:00:00 | end-23:59:59 ()
 * @return float|int|string
 */
function func_datetime_to_timestamp($datetime, string $timezone = DEFAULT_TIMEZONE, string $type = '')
{
    switch ($type) {
        default:
            return is_numeric($datetime)
                ? Carbon::parse($datetime)->setTimezone($timezone)->timestamp
                : Carbon::parse($datetime,$timezone)->timestamp;
        case 'start':
            return is_numeric($datetime)
                ? Carbon::parse($datetime)->setTimezone($timezone)->startOfDay()->timestamp
                : Carbon::parse($datetime,$timezone)->startOfDay()->timestamp;
        case 'end':
            return is_numeric($datetime)
                ? Carbon::parse($datetime)->setTimezone($timezone)->endOfDay()->timestamp
                : Carbon::parse($datetime,$timezone)->endOfDay()->timestamp;
    }
}

/**
 * IP地址解析国家
 * @param $ip
 * @param $type //默认 - 国家名称 | 'isoCode' - 国家码
 * @return string
 */
function func_ip_trans_country($ip, $type = '')
{
    $return = '';
    try {
        //加载IP库
        $reader = new Reader(base_path('public/geolite/GeoLite2-Country.mmdb'));
        //获取国家信息
        $record = $reader->country($ip);
        switch ($type) {
            //返回国家名称
            default:
                //返回国家名称
                $return = strtolower($record->country->name);
                break;
            //返回国家码
            case 'isoCode':
                $return = strtolower($record->country->isoCode);
                break;
        }
    }catch (Exception $e){
        //...
    }
    return $return;
}

/**
 * 处理逗号隔开的id参数
 * @param $ids
 * @return array
 */
function func_get_param_ids($ids)
{
    $ids = explode(',', $ids);
    $ids = array_filter($ids, function ($item) { //过滤、去重
        return is_numeric($item) && $item > 0;
    });
    $ids = array_values(array_unique($ids));
    return $ids;
}

/**
 * 二维数组排序
 * @param array $arr 需要排序的二维数组
 * @param string $keys 所根据排序的key
 * @param string $type 排序类型，desc、asc
 * @return array $new_array 排好序的结果
 */
function func_array_sort(array $arr, string $keys, $type = 'asc')
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($key_value);
    } else {
        arsort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 二维数组多个字段排序
 * @return mixed|null
 * @throws Exception
 */
function func_sort_array_by_many_field()
{
    $args = func_get_args(); // 获取函数的参数的数组
    if (empty($args)) {
        return null;
    }
    $arr = array_shift($args);
    if (!is_array($arr)) {
        throw new Exception("第一个参数不为数组");
    }
    foreach ($args as $key => $field) {
        if (is_string($field)) {
            $temp = array();
            foreach ($arr as $index => $val) {
                $temp[$index] = $val[$field];
            }
            $args[$key] = $temp;
        }
    }
    $args[] = &$arr;//引用值
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

/**
 * OSS地址补全
 * @param $url
 * @return string
 */
function func_full_oss_url($url)
{
    if (!is_string($url) || $url == '') {
        return '';
    }
    $pos = strstr($url, 'http');
    if ($pos) {
        return $url;
    }
    return rtrim(config('filesystems.disks.oss.domain'), '/') . '/' . ltrim($url, '/');
}

/**
 * json 转 array
 * @param $str
 * @return mixed
 */
function func_json_to_array($str)
{
    if (is_array($str)) return $str;
    if (is_null(json_decode((string)$str, true))) return $str;
    return json_decode((string)$str, true);
}

/**
 * 保留小数点
 * @param $number
 * @param int $decimals
 * @param $thousands_sep
 * @return string
 */
function number_point($number, $decimals = 2,$thousands_sep = ',')
{
    return number_format((float)$number, $decimals, '.', $thousands_sep);
}

/**
 * 随机验证码
 * @param $number
 * @return int
 */
function func_captcha_rand_code($number = 6)
{
    $min = 1;
    $max = 9;
    $number -= 1;
    for ($i = 0; $i < $number; $i++) {
        $min .= '0';
        $max .= '9';
    }
    return mt_rand((int)$min, (int)$max);
}

/**
 * POST请求
 * @param $data //string
 * @param $url
 * @param array $header
 * @param int $timeout
 * @return bool|string
 */
function func_curl_post($data, $url, $header = [], $timeout = 60)
{
    //数组要转成json字符串
    is_array($data) && $data = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //不自动输出任何内容到浏览器
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);          //只需要设置一个秒的数量就可以
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    // 设置header头
    if ($header) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/**
 * GET请求
 * @param $url
 * @param array $header
 * @param int $timeout
 * @return bool|string
 */
function func_curl_get($url, $header = [], $timeout = 60)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);          //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);   //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);            //只需要设置一个秒的数量就可以
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // 跳过证书检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
    // 设置header头
    if ($header) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}


/**
 * 毫秒级时间戳
 * @return float
 */
function func_get_millisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

/**
 * 求两个数的占比
 * @param $sub_count
 * @param $total_count
 * @param int $scale
 * @return int|string
 */
function func_get_rate($sub_count, $total_count, $scale = 2)
{
    $left = 0;
    if ($total_count != 0) {
        $left = bcdiv($sub_count, $total_count, 6);
    }
    return bcmul($left, 100, $scale);
}
############################# 公用 ↑ ↑ ↑ ##############################



############################# 后台 ↓ ↓ ↓  ##############################
/**
 * 后台当前组件路由
 * @return string
 */
function func_admin_router()
{
    $header = app('request')->headers;
    return $header->get('admin-router') ?: '';
}

############################# 后台 ↑ ↑ ↑ ##############################



############################# APP ↓ ↓ ↓  ##############################
/**
 * 客户端真实IP
 * @return string
 */
function func_app_ip()
{
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }elseif($_SERVER['REMOTE_ADDR']!=''){
        $ip = $_SERVER['REMOTE_ADDR'];
    }else{
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    return $ip;
}

/**
 * 手机号码脱敏
 * @param $mobile
 * @return string
 */
function func_mask_mobile($mobile)
{
    return Str::substr($mobile, 0, 3) . '****' . Str::substr($mobile, -4);
}

/**
 * 汉字脱敏
 * @param $str
 * @return string
 */
function func_mask_string($str) {
    $pattern = '/([\x{4e00}-\x{9fa5}]+)/u'; // 匹配中文字符的正则表达式
    preg_match_all($pattern, $str, $matches); // 获取所有中文字符
    $maskedStr = '';
    foreach ($matches[0] as $match) {
        $maskedStr .= str_repeat('*', mb_strlen($match, 'UTF-8')); // 将每个中文字符替换为相同数量的 *
    }
    return mb_substr($str, 0, 1, 'UTF-8') . $maskedStr . mb_substr($str, -1, 1, 'UTF-8'); // 保留头尾字符，替换中间部分为 *
}


############################# APP ↑ ↑ ↑ ##############################
