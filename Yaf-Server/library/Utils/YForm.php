<?php
/**
 * HTML 表单快捷生成工具。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Utils;

class YForm
{
    /**
     * 生成表单input标签。
     *
     * @param  string  $name       input的name的名称。
     * @param  string  $value      值。
     * @param  string  $className  class名称。
     * @param  string  $idName     id名称
     * @param  string  $isOutput   是否输出。
     * 
     * @return void|string
     */
    public static function input($name, $value = '', $className = '', $idName = '', $isOutput = true)
    {
        $value     = $value ?? '';
        $className = (strlen($className) > 0) ? " class=\"{$className}\"" : '';
        $idName    = (strlen($idName) > 0) ? " id=\"{$idName}\"" : '';
        $inputText = "<input name=\"{$name}\"{$className}{$idName} value=\"{$value}\" />";
        if ($isOutput) {
            echo $inputText;
        } else {
            return $inputText;
        }
    }

    /**
     * 生成表单textarea标签。
     *
     * @param  string  $name        textarea的name名称。
     * @param  string  $content     值。
     * @param  string  $className   css class 名称。
     * @param  string  $idName css  id 名称。
     * 
     * @return void|string
     */
    public static function textarea($name, $content = '', $className = '', $idName = '', $isOutput = true)
    {
        $content     = $content ?? '';
        $className   = (strlen($className) > 0) ? " class=\"{$className}\"" : '';
        $idName      = (strlen($idName) > 0) ? " id=\"{$idName}\"" : '';
        $selectOpen  = "<textarea name=\"{$name}\"{$className}{$idName}>";
        $selectClose = '</textarea>';
        $str         = "{$selectOpen}{$content}{$selectClose}";
        if ($isOutput) {
            echo $str;
        } else {
            return $str;
        }
    }

    /**
     * 生成表单的select标签。
     *
     * @param  string   $name           select的name名称。
     * @param  array    $data           下拉数据。
     * @param  string   $selectedValue  被选中的值。
     * @param  string   $className      class 名称。
     * @param  string   $idName         css id 名称。
     * @param  boolean  $isOutput       是否输出。true:是、false: 否。
     * 
     * @return void|string
     */
    public static function select($name, array $data, $selectedValue = null, $className = '', $idName = '', $isOutput = true)
    {
        if (empty($data)) {
            YCore::exception(- 1, '下拉数据不能为空');
        }
        $className    = (strlen($className) > 0) ? " class=\"{$className}\"" : '';
        $idName       = (strlen($idName) > 0) ? " id=\"{$idName}\"" : '';
        $selectOpen   = "<select name=\"{$name}\"{$className}{$idName}>";
        $selectClose  = '</select>';
        $selectOption = '';
        foreach ($data as $key => $item) {
            $key  = htmlspecialchars($key);
            $item = htmlspecialchars($item);
            if (strlen($selectedValue) > 0) {
                if ($selectedValue == $key) {
                    $selectOption .= "<option selected=\"selected\" value=\"{$key}\">{$item}</option>";
                } else {
                    $selectOption .= "<option value=\"{$key}\">{$item}</option>";
                }
            } else {
                $selectOption .= "<option value=\"{$key}\">{$item}</option>";
            }
        }
        $str = "{$selectOpen}{$selectOption}{$selectClose}";
        if ($isOutput) {
            echo $str;
        } else {
            return $str;
        }
    }
}