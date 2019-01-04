<?php
/**
 * 常量配置文件。
 * @author fingerQin
 * @date 2018-07-06
 */

/**
 * 512 在 PHP 内置错误码中存在，请勿使用。
 */
define('STATUS_SUCCESS', 200);
define('STATUS_ERROR', 500);
define('STATUS_SERVER_ERROR', 503);
define('STATUS_LOGIN_TIMEOUT', 901);  // 管理员:登录超时。
define('STATUS_NOT_LOGIN', 902);      // 管理员:未登录。
define('STATUS_OTHER_LOGIN', 903);    // 管理员:其他人登录。