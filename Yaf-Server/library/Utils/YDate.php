<?php
/**
 * 日期时间相关操作(时差/生肖/星座)。
 * 
 * @author fingerQin
 * @date 2018-06-28
 */

namespace Utils;

class YDate
{
    /**
     * 时间戳格式化。
     *
     * @param  int     $timestamp  时间戳。
     * @param  string  $format     时间输出格式。
     * @return string  $dot        当时间戳 为0的时候返回的值。
     * @return string
     */
    public static function formatTimestamp($timestamp, $format = 'Y-m-d H:i:s', $dot = '-')
    {
        if ($timestamp == 0) {
            return $dot;
        } else {
            return date($format, $timestamp);
        }
    }

    /**
     * 日期格式化。
     *
     * @param  string  $datetime  日期时间。
     * @param  string  $format    时间输出格式。
     * @return string  $dot       当时间戳 为0的时候返回的值。
     * @return string
     */
    public static function formatDateTime($datetime, $dot = '-')
    {
        if ($datetime == '0000-00-00 00:00:00' || !$datetime) {
            return $dot;
        } else {
            return $datetime;
        }
    }

    /**
     * 系统执行时间(微秒)。
     *
     * @return int
     */
    public static function getCostTime()
    {
        $microtime = microtime(TRUE);
        return $microtime - MICROTIME;
    }

    /**
     * 多久之前
     *
     * @param  string  $datetime  时间：2017-03-22 08:08:08
     * @return string
     */
    public static function howLongAgo($datetime)
    {
        $timestamp = strtotime($datetime);
        $seconds   = time();
        $time      = date('Y', $seconds) - date('Y', $timestamp);
        if ($time > 0) {
            if ($time == 1) {
                return '去年';
            } else {
                return $time . '年前';
            }
        }
        $time = date('m', $seconds) - date('m', $timestamp);
        if ($time > 0) {
            if ($time == 1) {
                return '上月';
            } else {
                return $time . '个月前';
            }
        }
        $time = date('d', $seconds) - date('d', $timestamp);
        if ($time > 0) {
            if ($time == 1) {
                return '昨天';
            } elseif ($time == 2) {
                return '前天';
            } else {
                return $time . '天前';
            }
        }
        $time = date('H', $seconds) - date('H', $timestamp);
        if ($time >= 1) {
            return $time . '小时前';
        }
        $time = date('i', $seconds) - date('i', $timestamp);
        if ($time >= 1) {
            return $time . '分钟前';
        }
        $time = date('s', $seconds) - date('s', $timestamp);
        return $time . '秒前';
    }

    /**
     * 根据生日中的月份和日期来计算所属星座*
     *
     * @param  int    $birthMonth
     * @param  int    $birthDate
     * @return string
     */
    public static function constellation($birthMonth, $birthDate)
    {
        // 判断的时候，为避免出现1和true的疑惑，或是判断语句始终为真的问题，这里统一处理成字符串形式
        $birthMonth = strval($birthMonth);
        $constellationName = [
            '水瓶座',
            '双鱼座',
            '白羊座',
            '金牛座',
            '双子座',
            '巨蟹座',
            '狮子座',
            '处女座',
            '天秤座',
            '天蝎座',
            '射手座',
            '摩羯座'
        ];
        if ($birthDate <= 22) {
            if ('1' !== $birthMonth) {
                $constellation = $constellationName[$birthMonth - 2];
            } else {
                $constellation = $constellationName[11];
            }
        } else {
            $constellation = $constellationName[$birthMonth - 1];
        }
        return $constellation;
    }

    /**
     * 根据生日中的年份来计算所属生肖
     *
     * @param  int    $birthYear
     * @param  int    $format    格式化形式。1-十二地支、2-十二生肖。
     * @return string
     */
    public static function animal($birthYear, $format = 1)
    {
        // 1900 年是子鼠年
        if ($format == '2') {
            $animal = ['子鼠', '丑牛', '寅虎', '卯兔', '辰龙', '巳蛇', '午马', '未羊', '申猴', '酉鸡', '戌狗', '亥猪'];
        } elseif ($format == '1') {
            $animal = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];
        }
        $myAnimal = ($birthYear - 1900) % 12;
        return $animal[$myAnimal];
    }
}