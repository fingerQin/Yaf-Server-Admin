<?php
/**
 * Session 操作。
 * @author fingerQin
 * @date 2018-09-14
 */

namespace Utils;

class YSession
{
    /**
     * 设置。
     *
     * @param  string        $Key    名称。
     * @param  string|array  $value  值。
     *
     * @return bool
     */
    public static function set($key, $value)
    {
        self::isOpenSession();
        return \Yaf_Registry::get('session')->set($key, $value);
    }

    /**
     * 读取 SESSION。
     *
     * @param  string  $key  名称。
     * 
     * @return bool
     */
    public static function get($key)
    {
        self::isOpenSession();
        return \Yaf_Registry::get('session')->get($key);
    }

    /**
     * 删除 SESSION。
     *
     * @param  string  $key  名称。
     * @return bool
     */
    public static function delete($key)
    {
        self::isOpenSession();
        return \Yaf_Registry::get('session')->del($key);
    }

    /**
     * 清空 SESSION。
     *
     * @param  string  $key  名称。
     * @return bool
     */
    public static function destroy()
    {
        self::isOpenSession();
        return session_destroy();
    }

    /**
     * 判断当前是否打开了 SESSION。
     * 
     * -- 未打开直接报错提示去打开 SESSION。
     */
    private static function isOpenSession()
    {
        if (!YCore::appconfig('session.status')) {
            YCore::exception(STATUS_ERROR, 'Please open the session switch : session.status');
        }
    }
}