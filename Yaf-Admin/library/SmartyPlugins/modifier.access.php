<?php

use Services\Power\Menu;
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */

/**
 * 菜单是否可访问。
 *
 * @param  string  $ctrl       控制器。
 * @param  string  $action     操作。
 *
 * @return bool
 */
function smarty_modifier_access($ctrl, $action)
{
    $cacheKey = 'admin_user_roleid';
    $roleid   = \Yaf_Registry::get($cacheKey);
    return Menu::checkRoleHasMenu($roleid, $ctrl, $action);
}