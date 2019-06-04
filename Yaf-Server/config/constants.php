<?php
/**
 * 常量配置文件。
 * @author fingerQin
 * @date 2018-06-27
 */

/**
 * 环境变量。
 */
define('ENV_DEV', 'dev');   // 开发环境。
define('ENV_PRE', 'pre');   // 预发布环境。
define('ENV_BETA', 'beta'); // 公测环境。
define('ENV_PRO', 'pro');   // 正式环境。

define('USER_ADDRESS_MAX_COUNT', 5); // 用户地址最大数量。

 /**
  * 错误码。
  * 请注意不要使用 512 做为错误码。PHP 内置错误当中可能会出现 512。
  */
define('STATUS_SUCCESS', 200);              // 请求成功。
define('STATUS_FORBIDDEN', 403);            // 没权限。
define('STATUS_NOT_FOUND', 404);            // 请求找不到。
define('STATUS_SERVER_ERROR', 503);         // 业务错误专用码。
define('STATUS_ERROR', 500);                // 服务器错误。
define('STATUS_LOGIN_TIMEOUT', 601);        // 用户登录超时。
define('STATUS_NOT_LOGIN', 602);            // 用户未登录。
define('STATUS_OTHER_LOGIN', 603);          // 其他人登录。
define('STATUS_ALREADY_REGISTER', 604);     // 账号已注册。
define('STATUS_UNREGISTERD', 605);          // 账号未注册。
define('STATUS_PASSWORD_EDIT', 606);        // 密码已经修改。 
define('STATUS_METHOD_NOT_EXISTS', 504);    // 接口 method 不存在特殊码。正式不记录日志。
define('STATUS_VERSION_NOT_EXISTS', 505);   // 接口版本号不存在特殊码。正式不记录日志。
define('STATUS_APPID_NOT_EXISTS', 506);     // 接口 appid 不存在特殊码。正式不记录日志。
define('STATUS_TIMESTAMP_NOT_EXISTS', 507); // 接口 timestamp 不存在特殊码。正式不记录日志。
define('STATUS_IP_FORBID', 508);            // 访问接口的 IP 不允许请求。
define('STATUS_API_NOT_EXISTS', 509);       // 访问的接口不存在。

// 正式环境不记录日志的错误码列表。
define('NO_RECORD_API_LIST', [
  STATUS_METHOD_NOT_EXISTS,
  STATUS_VERSION_NOT_EXISTS,
  STATUS_APPID_NOT_EXISTS,
  STATUS_TIMESTAMP_NOT_EXISTS,
  STATUS_IP_FORBID,
  STATUS_API_NOT_EXISTS
]);