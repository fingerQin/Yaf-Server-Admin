<?php
/**
 * Cookie 操作。
 * @author fingerQin
 * @date 2018-09-14
 */

namespace Utils;

class YCookie
{
    /**
     * Cookie 设置。
     *
     * @param  string  $name      名称。
     * @param  string  $value     值。
     * @param  int     $time      Cookie 过期时间。单位(秒)。设置为 0 代表浏览器关闭时 Cookie 失效。
     * @param  string  $domain    Cookie 生效域名。如何：admin.xxx.com。
     * @param  string  $path      Cookie 有效的服务器路径。
     * @param  string  $secure    设置这个 Cookie 是否仅仅通过安全的 HTTPS 连接传给客户端。
     * @param  bool    $httponly  是否仅能通过 HTTP 协议访问。
     *
     * @return bool
     */
    public static function set($name, $value, $time = 0, $domain = '', $path = '/', $secure = false, $httponly = false)
    {
        $value = YCore::sys_auth($value, 'ENCODE');
        $time  = ($time <= 0) ? 0 : (time() + $time);
        setcookie($name, $value, $time, $path, $domain, $secure, $httponly);
    }

    /**
     * Cookie 读取。
     *
     * @param  string  $name   名称。
     * @param  string  $value  默认值。当取不到的时候返回的值。
     * 
     * @return string
     */
    public static function get($name, $value = '')
    {
        if (isset($_COOKIE[$name])) {
            $cookie = $_COOKIE[$name];
            return YCore::sys_auth($cookie, 'DECODE');
        } else {
            return $value;
        }
    }

    /**
     * 获取所有 COOKIE。
     * 
     * -- 会剔出 PHP SESSION 会话的 COOKIE。
     *
     * @return array
     */
    public static function all()
    {
        $sessionName = session_name();
        $cookies     = empty($_COOKIE) ? [] : $_COOKIE;
        if (isset($cookies[$sessionName])) {
            unset($cookies[$sessionName]);
        }
        foreach ($cookies as $name => $cookie) {
            $cookies[$name] = YCore::sys_auth($cookie, 'DECODE');
        }
        return $cookies;
    }

    /**
     * Cookie 删除。
     *
     * @param  string  $name      名称。
     * @param  string  $domain    Cookie 生效域名。如何：admin.xxx.com。
     * @param  string  $path      Cookie 有效的服务器路径。
     * @param  string  $secure    设置这个 Cookie 是否仅仅通过安全的 HTTPS 连接传给客户端。
     * @param  bool    $httponly  是否仅能通过 HTTP 协议访问。
     *
     * @return bool
     */
    public static function delete($name, $domain = '', $path = '/', $secure = false, $httponly = false)
    {
        return setcookie($name, '', time()-3600, $path, $domain, $secure, $httponly);
    }

    /**
     * Cookie 清空。
     *
     * @param  string  $domain    Cookie 生效域名。如何：admin.xxx.com。
     * @param  string  $path      Cookie 有效的服务器路径。
     * @param  string  $secure    设置这个 Cookie 是否仅仅通过安全的 HTTPS 连接传给客户端。
     * @param  bool    $httponly  是否仅能通过 HTTP 协议访问。
     *
     * @return bool
     */
    public static function destroy($domain = '', $path = '/', $secure = false, $httponly = false)
    {
        $time = time();
        $sessionName = session_name();
        foreach ($_COOKIE as $name => $cookie) {
            if ($sessionName != $name) { // SESSION 对应的 COOKIE 不清除。
                return setcookie($name, null, $time-3600, $path, $domain, $secure, $httponly);
            }
        }
    }
}