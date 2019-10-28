<?php
/**
 * 管理后台帮助文档。
 * @author fingerQin
 * @date 2018-07-24
 */

namespace Services\Power;

use finger\Utils\YCore;
use Models\AdminMenu;

class Help extends \Services\AbstractBase
{
    /**
     * 获取帮助文档内容。
     *
     * @param  string  $ctrlName    控制器名称。
     * @param  string  $actionName  操作名称。
     *
     * @return string
     */
    public static function get($ctrlName, $actionName)
    {
        $where = [
            'c' => $ctrlName,
            'a' => $actionName
        ];
        $detail = (new AdminMenu())->fetchOne(['helpstr'], $where);
        return $detail ? $detail['helpstr'] : '';
    }

    /**
     * 设置帮助文档内容。
     *
     * @param  string  $ctrlName    控制器名称。
     * @param  string  $actionName  操作名称。
     * @param  string  $content     帮助文档内容。
     *
     * @return void
     */
    public static function set($ctrlName, $actionName, $content)
    {
        $where = [
            'c' => $ctrlName,
            'a' => $actionName
        ];
        $MenuModel = new AdminMenu();
        $detail    = $MenuModel->fetchOne(['helpstr'], $where);
        if (empty($detail)) {
            YCore::exception(STATUS_SERVER_ERROR, '添加失败');
        } else {
            return $MenuModel->update(['helpstr' => $content], $where);
        }
    }
}