<?php


namespace App\Traits;

/**
 * 生成一串随机字符串
 * 可加解密
 * Trait UniqueCode
 * @package App\Traits
 */
trait UniqueCode
{
    //密码字典
    private $dic = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

    /**
     * 生成
     * @param $int
     * @param int $format
     * @return string
     */
    public function encode($int, $format = 8)
    {
        $dics = $this->dic;
        $dnum = 36; //进制数
        $arr = array();
        $loop = true;
        while ($loop) {
            $index = (int)bcmod($int, $dnum);
            $arr[] = $dics[$index];
            $int = bcdiv($int, $dnum, 0);
            if ($int == '0') {
                $loop = false;
            }
        }
        if (count($arr) < $format)
            $arr = array_pad($arr, $format, $dics[0]);

        return implode('', array_reverse($arr));
    }

    public function decode($ids)
    {
        $dics = $this->dic;
        $dnum = 36; //进制数
        //键值交换
        $dedic = array_flip($dics);
        //去零
        $id = ltrim($ids, $dics[0]);
        //反转
        $id = strrev($id);
        $v = 0;
        for ($i = 0, $j = strlen($id); $i < $j; $i++) {
            $v = bcadd(bcmul($dedic[$id{
            $i}], bcpow($dnum, $i, 0), 0), $v, 0);
        }
        return $v;
    }

}

