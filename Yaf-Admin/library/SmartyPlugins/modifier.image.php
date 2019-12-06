<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */

use finger\Url;

/**
 * 获取静态资源URL。
 *
 * @param  string  $type              css、js、image
 * @param  string  $fileRelativePath  资源相对路径。如：/jquery/plugins/cookie.js
 * @return string
 */
function smarty_modifier_image($fileRelativePath)
{
    return Url::assets('image', $fileRelativePath);
}