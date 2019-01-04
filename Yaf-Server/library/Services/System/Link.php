<?php
/**
 * 友情链接。
 * @author fingerQin
 * @date 201-09-05
 */

namespace Services\System;

use Models\Link as LinkModel;
use finger\Database\Db;

class Link extends \Services\AbstractBase
{
    /**
     * 友情链接列表。
     *
     * @return array
     */
    public static function list()
    {
        // [1] 取出所有有效的友情链接。
        $sql = 'SELECT a.cat_id,a.link_name,a.link_url,a.image_url,b.cat_name '
             . 'FROM finger_link AS a INNER JOIN finger_category AS b ON(a.cat_id=b.cat_id) '
             . 'WHERE a.status = :status AND a.display = :display '
             . 'ORDER BY b.listorder ASC, a.listorder ASC, a.link_id ASC';
        $params = [
            ':status'  => LinkModel::STATUS_YES,
            ':display' => LinkModel::STATUS_YES
        ];
        $links  = Db::all($sql, $params);
        // [2] 友情链接分组组装。
        $result = [];
        foreach ($links as $link) {
            $linkItem = [
                'link_name' => $link['link_name'],
                'link_url'  => $link['link_url'],
                'image_url' => $link['image_url']
            ];
            if (isset($result[$link['cat_id']])) {
                $result[$link['cat_id']]['links'][] = $linkItem;
            } else {
                $result[$link['cat_id']] = [
                    'cat_id'   => $link['cat_id'],
                    'cat_name' => $link['cat_name'],
                    'links'    => [$linkItem]
                ];
            }
        }
        // [3] 去掉键值。其他语言 SDK 解析会很麻烦。
        $links = [];
        foreach ($result as $link) {
            $links[] = $link;
        }
        return $links;
    }
}