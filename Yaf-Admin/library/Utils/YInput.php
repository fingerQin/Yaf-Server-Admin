<?php
/**
 * 数据取值操作封装。
 * @author fingerQin
 * @date 2018-06-29
 */

namespace Utils;

use finger\Validator;

class YInput
{
    /**
     * 从数组中读取一个数组。
     *
     * @param  array   $data          数组。
     * @param  string  $name          参数名称。
     * @param  array   $defaultValue  默认值。
     * @return array
     */
    public static function getArray($data, $name, $defaultValue = null)
    {
        if (!is_array($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '值为空');
        }
        if (!isset($data[$name])) {
            if (is_null($defaultValue)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} cannot be empty");
            } else if (!is_array($defaultValue)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} of the default value is not a array");
            } else {
                return $defaultValue;
            }
        } else {
            $value = $data[$name];
            if (!is_array($value)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} value is not a array");
            } else {
                return $value;
            }
        }
    }

    /**
     * 从数组中读取一个整型数值。
     *
     * @param  array   $data          数组。
     * @param  string  $name          参数名称。
     * @param  int     $defaultValue  默认值。
     * @return int
     */
    public static function getInt($data, $name, $defaultValue = null)
    {
        if (!is_array($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '值为空');
        }
        if (!isset($data[$name])) {
            if (is_null($defaultValue)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} cannot be empty");
            } else if (!Validator::is_integer($defaultValue)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} of the default value is not a integer");
            } else {
                return $defaultValue;
            }
        } else {
            $value = $data[$name];
            if (!Validator::is_integer($value)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} value is not a integer");
            } else {
                return $value;
            }
        }
    }

    /**
     * 从数组中读取一个字符串数值。
     *
     * @param  array   $data          数组。
     * @param  string  $name          参数名称。
     * @param  string  $defaultValue  默认值。
     * @return string
     */
    public static function getString($data, $name, $defaultValue = null)
    {
        if (!is_array($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '值为空');
        }
        if (!isset($data[$name])) {
            if (is_null($defaultValue)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} cannot be empty");
            } else {
                return $defaultValue;
            }
        } else {
            return $data[$name];
        }
    }

    /**
     * 从数组中读取一个整型数值。
     *
     * @param  array   $data          数组。
     * @param  string  $name          参数名称。
     * @param  float   $defaultValue  默认值。
     * @return float
     */
    public static function getFloat($data, $name, $defaultValue = null)
    {
        if (!is_array($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '值为空');
        }
        if (!isset($data[$name])) {
            if (is_null($defaultValue)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} cannot be empty");
            } else if (!Validator::is_float($defaultValue)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} of the default value is not a float");
            } else {
                return $defaultValue;
            }
        } else {
            $value = $data[$name];
            if (!Validator::is_float($value)) {
                YCore::exception(STATUS_SERVER_ERROR, "{$name} value is not a float");
            } else {
                return $value;
            }
        }
    }
}