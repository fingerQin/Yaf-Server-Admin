<?php
/**
 * 字符串操作(安全过滤)。
 * 
 * @author fingerQin
 * @date 2018-06-28
 */

namespace Utils;

class YString
{
    /**
     * 字符串星号处理器。
     * 
     * @param  string  $str     被加星处理的字符串。
     * @param  int     $start   星号起始位置。
     * @param  int     $length  星号长度。
     * @return string
     */
    public static function asterisk($str, $start, $length = 0)
    {
        $strLength = mb_strlen($str, 'UTF-8');
        $startStr  = ''; // 头部的字符串。
        $endStr    = ''; // 尾部的字符串。
        $asterisk  = ''; // 星号部分。
        $start     = $start >= 0 ? $start : 0;
        $start     = $start > $strLength ? $strLength : $start;
        $safeLen   = $strLength - $start; // 剩余可以被星号处理的安全长度。
        $length    = ($length <= $safeLen) ? $length : $safeLen;
        $length    = $length <= 0 ? $safeLen : $length;
        if ($start > 0) {
            $startStr = mb_substr($str, 0, $start, 'UTF-8');
        }
        if ($length != $safeLen) {
            $endStr = mb_substr($str, $start + $length, $length, 'UTF-8');;
        }
        $asterisk = str_repeat('*', $length);
        return $startStr . $asterisk . $endStr;
    }

    /**
     * 生成随机字符串
     *
     * @param  string  $lenth  长度
     * @return string 字符串
     */
    public static function randomstr($lenth = 6)
    {
        return self::random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
    }

    /**
     * 产生随机字符串
     *
     * @param  int     $length  输出长度
     * @param  string  $chars   可选的，默认为 0123456789
     * 
     * @return string 字符串
     */
    public static function random($length, $chars = '0123456789')
    {
        $hash = '';
        $max  = strlen($chars) - 1;
        for($i = 0; $i < $length; $i ++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    /**
     * 字符截取支持UTF8。
     *
     * @param  string  $string
     * @param  int     $length
     * @param  string  $dot
     * @return string
     */
    public static function str_cut($string, $length, $dot = '...')
    {
        $strlen = strlen($string);
        if ($strlen <= $length) {
            return $string;
        }
        $string = str_replace([
            ' ', '&nbsp;', '&amp;', '&quot;', '&#039;',
            '&ldquo;', '&rdquo;', '&mdash;', '&lt;',
            '&gt;', '&middot;', '&hellip;'
        ], 
        [
            '∵', ' ', '&', '"', "'",
            '“', '”', '—', '<', '>', '·', '…'
        ], $string);
        $strcut = '';
        $length = intval($length - strlen($dot) - $length / 3);
        $n = $tn = $noc = 0;
        while ($n < strlen($string) ) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n ++;
                $noc ++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n ++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
        $strcut = str_replace(['∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'], 
        [' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'], $strcut);
        return $strcut . $dot;
    }

    /**
     * xss 过滤函数
     *
     * @param  string  $string
     * @return string
     */
    public static function remove_xss($string)
    {
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);
        $parm1 = [
            'javascript',
            'vbscript',
            'expression',
            'applet',
            'meta',
            'xml',
            'blink',
            'link',
            'script',
            'embed',
            'object',
            'iframe',
            'frame',
            'frameset',
            'ilayer',
            'layer',
            'bgsound',
            'title',
            'base'
        ];
        $parm2 = [
            'onabort',
            'onactivate',
            'onafterprint',
            'onafterupdate',
            'onbeforeactivate',
            'onbeforecopy',
            'onbeforecut',
            'onbeforedeactivate',
            'onbeforeeditfocus',
            'onbeforepaste',
            'onbeforeprint',
            'onbeforeunload',
            'onbeforeupdate',
            'onblur',
            'onbounce',
            'oncellchange',
            'onchange',
            'onclick',
            'oncontextmenu',
            'oncontrolselect',
            'oncopy',
            'oncut',
            'ondataavailable',
            'ondatasetchanged',
            'ondatasetcomplete',
            'ondblclick',
            'ondeactivate',
            'ondrag',
            'ondragend',
            'ondragenter',
            'ondragleave',
            'ondragover',
            'ondragstart',
            'ondrop',
            'onerror',
            'onerrorupdate',
            'onfilterchange',
            'onfinish',
            'onfocus',
            'onfocusin',
            'onfocusout',
            'onhelp',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onlayoutcomplete',
            'onload',
            'onlosecapture',
            'onmousedown',
            'onmouseenter',
            'onmouseleave',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onmousewheel',
            'onmove',
            'onmoveend',
            'onmovestart',
            'onpaste',
            'onpropertychange',
            'onreadystatechange',
            'onreset',
            'onresize',
            'onresizeend',
            'onresizestart',
            'onrowenter',
            'onrowexit',
            'onrowsdelete',
            'onrowsinserted',
            'onscroll',
            'onselect',
            'onselectionchange',
            'onselectstart',
            'onstart',
            'onstop',
            'onsubmit',
            'onunload'
        ];
        $parm  = array_merge($parm1, $parm2);
        for($i = 0; $i < sizeof($parm); $i ++) {
            $pattern = '/';
            for($j = 0; $j < strlen($parm[$i]); $j ++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0([9][a][b]);?)?';
                    $pattern .= '|(&#0([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $parm[$i][$j];
            }
            $pattern .= '/i';
            $string = preg_replace($pattern, '', $string);
        }
        return $string;
    }

    /**
     * 转义 javascript 代码标记
     *
     * @param  string  $str
     * @return mixed
     */
    public static function trim_script($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = self::trim_script($val);
            }
        } else {
            $str = preg_replace('/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str);
            $str = preg_replace('/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str);
            $str = preg_replace('/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str);
            $str = str_replace('javascript:', 'javascript：', $str);
        }
        return $str;
    }

    /**
     * 安全过滤函数
     *
     * @param  string  $string
     * @return string
     */
    public static function safe_replace($string)
    {
        $string = str_replace('%20', '', $string);
        $string = str_replace('%27', '', $string);
        $string = str_replace('%2527', '', $string);
        $string = str_replace('*', '', $string);
        $string = str_replace('"', '&quot;', $string);
        $string = str_replace("'", '', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('<', '&lt;', $string);
        $string = str_replace('>', '&gt;', $string);
        $string = str_replace("{", '', $string);
        $string = str_replace('}', '', $string);
        $string = str_replace('\\', '', $string);
        return $string;
    }

    /**
     * 将文本格式成适合js输出的字符串
     *
     * @param  string   $string 需要处理的字符串
     * @param  int      $isjs   是否执行字符串格式化，默认为执行
     * @return string 处理后的字符串
     */
    public static function format_js($string, $isjs = 1)
    {
        $string = addslashes(str_replace(["\r", "\n", "\t"], ['', '', ''], $string));
        return $isjs ? 'document.write("' . $string . '");' : $string;
    }

    /**
     * 格式化文本域内容
     *
     * @param  string  $string 文本域内容
     * @return string
     */
    public static function trim_textarea($string)
    {
        $string = nl2br(str_replace(' ', '&nbsp;', $string));
        return $string;
    }

    /**
     * 过滤ASCII码从0-28的控制字符
     *
     * @return String
     */
    public static function trim_unsafe_control_chars($str)
    {
        $rule = '/[' . chr(1) . '-' . chr(8) . chr(11) . '-' . chr(12) . chr(14) . '-' . chr(31) . ']*/';
        return str_replace(chr(0), '', preg_replace($rule, '', $str));
    }
}