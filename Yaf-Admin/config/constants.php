<?php
/**
 * 常量配置文件。
 * @author fingerQin
 * @date 2018-07-06
 */

define('STATUS_SUCCESS', 200);
define('STATUS_ERROR', 500);
define('STATUS_SERVER_ERROR', 503);
define('STATUS_LOGIN_TIMEOUT', 901);  // 管理员:登录超时。
define('STATUS_NOT_LOGIN', 902);      // 管理员:未登录。
define('STATUS_OTHER_LOGIN', 903);    // 管理员:其他人登录。

define('ROOT_ADMIN_ID', 1); // 管理后台超级管理员的 ID。
define('ROOT_ROLE_ID', 1);  // 管理后台超级管理员角色的 ID。