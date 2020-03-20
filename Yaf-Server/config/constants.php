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

define('LOGIN_ACCOUNT_PWD_ERROR_TIMES_LOCK', 5);  // 登录密码错误次数达该值则锁定。
define('LOGIN_PWD_ERROR_LOCK_TIME', 86400);       // 登录超过设定次数，则锁定该时间(单位:秒)。

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
define('STATUS_lOGIN_PWD_ERR_FORBID', 607); // 登录密码错误被限制登录 24 小时。
define('STATUS_METHOD_NOT_EXISTS', 504);    // 接口 method 不存在特殊码。正式不记录日志。
define('STATUS_VERSION_NOT_EXISTS', 505);   // 接口版本号不存在特殊码。正式不记录日志。
define('STATUS_APPID_NOT_EXISTS', 506);     // 接口 appid 不存在特殊码。正式不记录日志。
define('STATUS_TIMESTAMP_NOT_EXISTS', 507); // 接口 timestamp 不存在特殊码。正式不记录日志。
define('STATUS_IP_FORBID', 508);            // 访问接口的 IP 不允许请求：访问特殊类型 API 接口。
define('STATUS_API_NOT_EXISTS', 509);       // 访问的接口不存在。
define('STATUS_ACCESS_FORBID_IP', 510);     // 整个系统访问受限制的疑似非法 IP。
define('STATUS_SMS_CODE_ERROR', 700);       // 短信验证码不正确。

/**
 * 正式环境不记录错误日志的错误码列表。
 * -- 注1：因为此类错误码大概率是因为非法访问、伪造请求、暴力破解等造成。所以，不记录日志是为了避免日志污染膨胀。
 * -- 注2：请求日志依然会记录。
 */
define('NO_RECORD_API_LIST', [
  STATUS_METHOD_NOT_EXISTS,
  STATUS_VERSION_NOT_EXISTS,
  STATUS_APPID_NOT_EXISTS,
  STATUS_TIMESTAMP_NOT_EXISTS,
  STATUS_IP_FORBID,
  STATUS_API_NOT_EXISTS,
  STATUS_ACCESS_FORBID_IP,
  STATUS_lOGIN_PWD_ERR_FORBID
]);

/**
 * 指定必须接受 IP 异常限制的接口列表。
 * -- 注1：只读型接口通常不需要限制。
 * -- 注2：敏感型或涉及数据隐私泄漏的可以加入。比如：登录接口：账号密码被撞库泄漏。
 */
define('API_MUST_FORBID_IP_LIST', [
  'user.login',     // 登录接口：涉及撞库。
  'user.register',
  'sms.send',
  'user.pwd.find'
]);

/**
 * API 接口当中有一些特殊的敏感信息需要进行过滤(加密)之后保存。
 */
define('API_SAFETY_FILTER', [
  'oriJson',    // API 接口中的原始请求数据。
  'password',   // 密码。
  'old_pwd',    // 旧密码。
  'new_pwd',    // 新密码。
  'token',      // TOKEN 会话令牌。
  'mobile',     // 手机号。
  'sms_code',   // 短信验证码。
  'realname',   // 收货人姓名/真实姓名。
]);