<?php
/**
 * 数据操作封装。
 * @author fingerQin
 * @date 2018-06-28
 */

namespace Utils;

class YArray
{
    /**
     * 数组转换为树。
     *
     * @param  array  $sourceArr    源数组。
     * @param  string $key          数组主键名称。
     * @param  string $parentKey    数组父id键名称。
     * @param  string $childrenKey  生成的子树键名称。
     * @return array
     */
    public static function arrty_to_tree($sourceArr, $key, $parentKey, $childrenKey)
    {
        $tempSrcArr = [];
        foreach ($sourceArr as $v) {
            $tempSrcArr[$v[$key]] = $v;
        }
        $i = 0;
        $count = count($sourceArr);
        for($i = ($count - 1); $i >= 0; $i --) {
            if (isset($tempSrcArr[$sourceArr[$i][$parentKey]])) {
                $tArr = array_pop($tempSrcArr);
                $tempSrcArr[$tArr[$parentKey]][$childrenKey] = (isset($tempSrcArr[$tArr[$parentKey]][$childrenKey]) && is_array($tempSrcArr[$tArr[$parentKey]][$childrenKey])) ? $tempSrcArr[$tArr[$parentKey]][$childrenKey] : [];
                array_push($tempSrcArr[$tArr[$parentKey]][$childrenKey], $tArr);
            }
        }
        // 最外层关联索引转换为数字索引，这样在json转换的时候是list,而非对象。
        $_items = [];
        foreach ($tempSrcArr as $_temp_item) {
            $_items[] = $_temp_item;
        }
        return $_items;
    }

     /**
     * 对数据进行编码转换
     *
     * @param  array|string  $data    数组
     * @param  string        $input   需要转换的编码
     * @param  string        $output  转换后的编码
     * @return string|array
     */
    public static function array_iconv($data, $input = 'gbk', $output = 'utf-8')
    {
        if (!is_array($data)) {
            return iconv($input, $output, $data);
        } else {
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    $data[$key] = self::array_iconv($val, $input, $output);
                } else {
                    $data[$key] = iconv($input, $output, $val);
                }
            }
            return $data;
        }
    }

    /**
     * 移除两个数组中相同的元素并返回不同部分的数组。
     *
     * @param  array  $array1
     * @param  array  $array2
     * @return array
     */
    public static function array_remove_equal(array $array1, array $array2)
    {
        $diffArray = [];
        foreach ($array1 as $val) {
            if (!in_array($val, $array2)) {
                $diffArray[] = $val;
            }
        }
        foreach ($array2 as $val) {
            if (!in_array($val, $array1)) {
                $diffArray[] = $val;
            }
        }
        return $diffArray;
    }
}