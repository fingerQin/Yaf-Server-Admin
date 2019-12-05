<?php
/**
 * 用户权限封装。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services\User;

use finger\App;
use finger\Utils\YCache;
use finger\Utils\YString;
use finger\Utils\YCore;
use Models\Event;
use Models\User as UserModel;
use finger\Validator;
use Services\Sms\Sms;
use Services\System\Push;
use Services\Event\Producer;
use Services\AccessForbid\Forbid;

class Auth extends \Services\AbstractBase
{
    /**
     * 登录类型。
     * -- 支持短信验证码与账号密码两种登录方式。
     */
    const LOGIN_TYPE_SMS = 1; // 短信验证码登录。
    const LOGIN_TYPE_PWD = 2; // 密码登录。

    /**
     * 真正登录。
     *
     * @param  string  $mobile       手机号。
     * @param  int     $loginType    登录类型。1-验证码登录、2-密码登录。
     * @param  string  $code         验证码。
     * @param  string  $platform     平台。 1-IOS|2-Android|3-WAP|4-PC端。
     * @param  string  $appV         APP 版本号。
     * @param  string  $deviceToken  信鸽分配给手机的设备 TOKEN。
     * @param  string  $v            API 版本号。默认值空字符串。
     *
     * @return array
     */
    public static function login($mobile, $loginType, $code, $platform, $appV, $deviceToken, $v = '')
    {
        self::checkLoginPwdErrForbidLogin($mobile);
        $userinfo = (new UserModel())->fetchOne([], ['mobile' => $mobile]);
        if (empty($userinfo)) {
            Forbid::position(Forbid::POSITION_LOGIN, 50, 30);
            YCore::exception(STATUS_UNREGISTERD, '账号不存在!');
        }
        if ($userinfo['cur_status'] == UserModel::STATUS_INVALID) {
            YCore::exception(STATUS_SERVER_ERROR, '账号不存在或已经注销!');
        }
        if ($userinfo['cur_status'] == UserModel::STATUS_LOCKED) {
            YCore::exception(STATUS_SERVER_ERROR, '您的账号被锁定!');
        }
        if ($loginType == self::LOGIN_TYPE_SMS) {
            self::loginSmsCodeVerify($mobile, $code, $loginType);
        } else {
            self::loginUserPwdVerify($code, $userinfo, $loginType);
        }
        self::clearLoginPwdErrCounter($mobile);
        $token = self::createToken($userinfo['userid'], $userinfo['pwd'], TIMESTAMP, $platform);
        self::setAuthTokenLastAccessTime($userinfo['userid'], $token, $platform);
        Push::registerUserAssocDeviceToken($userinfo['userid'], $deviceToken, $platform, $appV);
        Producer::push([
            'code'        => Event::CODE_LOGIN,
            'userid'      => $userinfo['userid'],
            'mobile'      => $mobile,
            'platform'    => $platform,
            'app_v'       => $appV,
            'v'           => $v,
            'login_time'  => date('Y-m-d H:i:s', TIMESTAMP)
        ]);
        return [
            'token'    => $token,
            'open_id'  => $userinfo['open_id'],
            'mobile'   => $userinfo['mobile'],
            'headimg'  => $userinfo['headimg'],
            'nickname' => $userinfo['nickname'],
            'intro'    => $userinfo['intro'],
            'reg_time' => $userinfo['c_time']
        ];
    }

    /**
     * 登录短信验证码验证。
     *
     * @param  string  $mobile     手机号。
     * @param  string  $code       验证码。
     * @param  int     $loginType  登录类型。1-验证码登录、2-密码登录。
     *
     * @return void
     */
    private static function loginSmsCodeVerify($mobile, $code, $loginType)
    {
        try {
            Sms::verify($mobile, $code, Sms::SMS_TYPE_LOGIN);
        } catch (\Exception $e) {
            if ($e->getCode() == STATUS_SMS_CODE_ERROR) {
                Forbid::position(Forbid::POSITION_LOGIN, 50, 30);
                self::loginPwdErrCounter($mobile, $loginType);
            }
            YCore::exception($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 登录时用户密码验证。
     *
     * @param  string  $password   登录密码。
     * @param  array   $userinfo   用户基本信息(包含：salt、pwd)。
     * @param  int     $loginType  登录类型。1-验证码登录、2-密码登录。
     *
     * @return void
     */
    private static function loginUserPwdVerify($password, $userinfo, $loginType)
    {
        if (strlen($password) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '密码必须填写!');
        }
        $password = self::encryptPwd($password, $userinfo['salt']);
        if ($password != $userinfo['pwd']) {
            Forbid::position(Forbid::POSITION_LOGIN, 50, 30);
            self::loginPwdErrCounter($userinfo['mobile'], $loginType);
            YCore::exception(STATUS_SERVER_ERROR, '密码不正确!');
        }
    }

    /**
     * 账号连续密码错误计数器。
     *
     * -- 账号密码连续错误之后锁定 24 小时。
     *
     * @param  string  $mobile     手机账号。
     * @param  int     $loginType  登录类型。1-验证码登录、2-密码登录。
     * 
     * @return void
     */
    private static function loginPwdErrCounter($mobile, $loginType)
    {
        $counter = "login_account_lock_{$mobile}";
        $redis   = YCache::getRedisClient();
        $redis->set($counter, 0, ['NX', 'EX' => LOGIN_PWD_ERROR_LOCK_TIME]);
        $int     = $redis->incr($counter);
        if ($int >= LOGIN_ACCOUNT_PWD_ERROR_TIMES_LOCK) {
            $lastDeadlineKey = "login_account_unlock_date_{$mobile}";
            $datetime = date('Y-m-d H:i:s', TIMESTAMP + LOGIN_PWD_ERROR_LOCK_TIME);
            $redis->set($lastDeadlineKey, $datetime, LOGIN_PWD_ERROR_LOCK_TIME);
            if ($loginType == 1) {
                YCore::exception(STATUS_lOGIN_PWD_ERR_FORBID, "验证码错误" . LOGIN_ACCOUNT_PWD_ERROR_TIMES_LOCK . "次被禁止登录,解禁时间：{$datetime}");
            } else {
                YCore::exception(STATUS_lOGIN_PWD_ERR_FORBID, "账号密码错误" . LOGIN_ACCOUNT_PWD_ERROR_TIMES_LOCK . "次被禁止登录,解禁时间：{$datetime}");
            }
        }
    }

    /**
     * 清除账号连续密码错误计数器。
     *
     * @param  string  $mobile  手机号。
     * @return void
     */
    private static function clearLoginPwdErrCounter($mobile)
    {
        $counter = "login_account_lock_{$mobile}";
        $redis   = YCache::getRedisClient();
        $redis->del($counter);
    }

    /**
     * 检查登录密码错误次数是否触发禁止登录。
     *
     * @param  string  $mobile  手机号。
     *
     * @return void
     */
    private static function checkLoginPwdErrForbidLogin($mobile)
    {
        $counter = "login_account_lock_{$mobile}";
        $redis   = YCache::getRedisClient();
        $int     = $redis->get($counter);
        if ($int >= LOGIN_ACCOUNT_PWD_ERROR_TIMES_LOCK) {
            $lastDeadlineKey = "login_account_unlock_date_{$mobile}";
            $deadline = $redis->get($lastDeadlineKey);
            if ($deadline !== FALSE) {
                YCore::exception(STATUS_lOGIN_PWD_ERR_FORBID, "账号已冻结登录，解禁时间：{$deadline}");
            } else {
                YCore::exception(STATUS_lOGIN_PWD_ERR_FORBID, "您的账号被禁止登录,请明天重试");
            }
        }
    }

    /**
     * 注册。
     *
     * @param  string  $mobile       手机号码。 
     * @param  string  $code         短信验证码。
     * @param  string  $password     密码。
     * @param  string  $platform     平台。 |1-IOS|2-Android|3-WAP|4-PC端。
     * @param  string  $channel      渠道。安卓 APP 才会有渠道。
     * @param  string  $appV APP     版本号。
     * @param  string  $deviceToken  信鸽分配给手机的设备 TOKEN。
     * @param  string  $v            API 版本号。默认为空字符串。
     * @param  string  $activityId   活动ID。默认为空字符串。
     * @param  string  $inviteUser   邀请人 openid。默认值为空空字符串。
     *
     * @return array
     */
    public static function register(
        $mobile, 
        $code, 
        $password, 
        $platform, 
        $channel, 
        $appV, 
        $deviceToken, 
        $v = '',
        $activityId = '', 
        $inviteUser = ''
    )
    {
        // [1]
        $data = [
            'mobile'   => $mobile,
            'password' => $password,
            'code'     => $code,
        ];
        $rules = [
            'mobile'   => '手机号|require|mobilephone',
            'password' => '密码|require|alpha_dash|len:6:20:0',
            'code'     => '验证码|require|number|len:6:6:0',
        ];
        Validator::valido($data, $rules);
        // [2]
        if (self::isRegister($mobile)) {
            YCore::exception(STATUS_ALREADY_REGISTER, '您的账号已经被占用!');
        }
        Sms::verify($mobile, $code, Sms::SMS_TYPE_REGISTER);
        $datetime = date('Y-m-d H:i:s', TIMESTAMP);
        $nickname = YString::asterisk($mobile, 3, 4);
        $salt     = YString::randomstr(6);
        $openid   = self::makeUserOpenId($mobile);
        $MUser    = new UserModel();
        $data     = [
            'open_id'         => $openid,
            'mobile'          => $mobile,
            'nickname'        => $nickname,
            'platform'        => $platform,
            'app_market'      => $channel,
            'salt'            => $salt,
            'pwd'             => self::encryptPwd($password, $salt),
            'cur_status'      => UserModel::STATUS_YES,
            'c_time'          => $datetime,
            'last_login_time' => $datetime,
            'last_login_ip'   => YCore::ip()
        ];
        $userid = $MUser->insert($data);
        if (!$userid) {
            YCore::exception(STATUS_SERVER_ERROR, '注册失败');
        }
        $token = self::createToken($userid, TIMESTAMP, $platform);
        self::setAuthTokenLastAccessTime($userid, $token, $platform);
        Push::registerUserAssocDeviceToken($userid, $deviceToken, $platform, $appV);
        Producer::push([
            'code'        => Event::CODE_REGISTER,
            'userid'      => $userid,
            'mobile'      => $mobile,
            'platform'    => $platform,
            'app_v'       => $appV,
            'v'           => $v,
            'reg_time'    => $datetime,
            'activity_id' => $activityId,
            'invite_user' => $inviteUser,
        ]);
        return [
            'token'    => $token,
            'mobile'   => $mobile,
            'headimg'  => '',
            'nickname' => $nickname,
            'reg_time' => $datetime,
            'intro'    => '',
            'open_id'  => $openid
        ];
    }

    /**
     * 密码加密。
     *
     * @param  string  $password  密码原文。
     * @param  string  $salt      密码址。
     * @return string
     */
    public static function encryptPwd($password, $salt)
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 是否注册。
     *
     * @param  string  $mobile  手机号码。
     * @return bool
     */
    public static function isRegister($mobile)
    {
        $userinfo = (new UserModel())->fetchOne([], ['mobile' => $mobile]);
        return $userinfo ? true : false;
    }

    /**
     * 退出登录。
     *
     * --1、退出登录要清除推送的关联。
     *
     * @param  string  $userid    用户ID。
     * @param  string  $platform  平台。
     * @return void
     */
    public static function logout($userid, $platform)
    {
        $loginType     = self::isAppCall($platform) ? 1 : 0;
        $cacheKeyToken = "u_t_k:{$loginType}:{$userid}";
        YCache::delete($cacheKeyToken);
        Push::clearUserAssocDeviceToken($userid);
    }

    /**
     * 检查用户(token)权限。
     *
     * -- 1、在每次用户访问程序的时候调用。
     *
     * @param  string  $token  会话 Token。
     * @return array
     */
    public static function checkAuth($token)
    {
        // [1] 参数判断。
        if (strlen($token) === 0) {
            YCore::exception(STATUS_NOT_LOGIN, '账号未登录');
        }
        // [2] token解析
        $tokenParams = self::parseToken($token);
        $userid      = $tokenParams['userid'];
        $password    = $tokenParams['password'];
        $platform    = $tokenParams['platform'];
        // [3] 用户存在与否判断
        $userinfo = (new UserModel())->fetchOne([], ['userid' => $userid]);
        if (empty($userinfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '系统异常!');
        }
        if ($userinfo['cur_status'] == UserModel::STATUS_INVALID) {
            YCore::exception(STATUS_SERVER_ERROR, '账号不存在或已经注销!');
        }
        if ($userinfo['cur_status'] == UserModel::STATUS_LOCKED) {
            YCore::exception(STATUS_SERVER_ERROR, '您的账号被锁定!');
        }
        // [4] token 是否超出了超时时限
        $loginType     = self::isAppCall($platform) ? 1 : 0; // 1 APP 客户端登录、0 - 非 APP 客户端登录。
        $cacheKeyToken = "u_t_k:{$loginType}:{$userid}";
        $cacheToken    = YCache::get($cacheKeyToken);
        if ($cacheToken === false) {
            YCore::exception(STATUS_LOGIN_TIMEOUT, '登录超时,请重新登录');
        }
        if ($cacheToken === null) {
            YCore::exception(STATUS_LOGIN_TIMEOUT, '登录超时,请重新登录');
        }
        if ($token != $cacheToken) {
            YCore::exception(STATUS_OTHER_LOGIN, '您的账号在其它地方登录');
        }
        if ($userinfo['pwd'] != $password) {
            YCore::exception(STATUS_PASSWORD_EDIT, '您的密码已经修改,请重新登录!');
        }
        self::setAuthTokenLastAccessTime($userid, $token, $platform);
        return [
            'userid' => $userid,
            'mobile' => $userinfo['mobile']
        ];
    }

    /**
     * 获取会话 token 中的用户ID。
     *
     * -- 如果 token 存在且有效就解析得到用户ID。否则返回0。
     *
     * @param  string  $token  用户会话 token。
     * @return int
     */
    public static function getTokenUserId($token)
    {
        try {
            if (strlen($token) === 0) {
                return 0;
            }
            $result   = self::parseToken($token);
            $userid   = intval($result['userid']);
            $userinfo = (new UserModel())->fetchOne([], ['userid' => $userid]);
            if (empty($userinfo)) {
                return 0;
            }
            if ($userinfo['cur_status'] == UserModel::STATUS_INVALID) {
                return 0;
            }
            if ($userinfo['cur_status'] == UserModel::STATUS_LOCKED) {
                return 0;
            }
            if ($userinfo['cur_status'] == UserModel::STATUS_FREEZE) {
                return 0;
            }
            if ($userinfo['pwd'] != $result['password']) {
                return 0;
            }
        } catch (\Exception $e) {
            $userid = 0;
        }
        return $userid;
    }

    /**
     * 刷新用户会话 token。
     *
     * -- token 有效就刷新。无效就不刷新。
     * -- 刷新成功后返回具体的刷新状态。
     *
     * @param  string $token 用户会话 token。
     * @return bool
     */
    public static function refreshToken($token)
    {
        try {
            $tokenParams = self::parseToken($token);
            $userid      = $tokenParams['userid'];
            $platform    = $tokenParams['platform'];
            self::setAuthTokenLastAccessTime($userid, $token, $platform);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 生成 Token。
     *
     * @param  int     $userid     用户ID。
     * @param  string  $password   密码（加密后的）。
     * @param  int     $loginTime  登录时间(时间戳)。
     * @param  int     $platform   平台标识。1-IOS|2-Android|4-h5|5-web。
     * @return string
     */
    protected static function createToken($userid, $password, $loginTime, $platform = 0)
    {
        $env = App::getConfig('app.env');
        $str = "{$userid}\t{$password}\t{$loginTime}\t{$platform}\t{$env}";
        return YCore::sys_auth($str, 'ENCODE', '', 0);
    }

    /**
     * 设置 auth_token 最后的访问时间。
     *
     * @param  int     $userid    用户ID。
     * @param  string  $token     会话 TOKEN。
     * @param  int     $platform  平台标识。1-IOS|2-Android|4-h5|5-web。
     * @return void
     */
    protected static function setAuthTokenLastAccessTime($userid, $authToken, $platform = 0)
    {
        $loginType = self::isAppCall($platform) ? 1 : 0;    // 1 APP 客户端登录、0 非 APP 客户端登录。
        $cacheTime = $loginType ? 30 * 86400 : 1800;
        $cacheKey  = "u_t_k:{$loginType}:{$userid}";        // 用户保存 auth_token 的缓存键。
        YCache::set($cacheKey, $authToken, $cacheTime);
    }

    /**
	 * 解析 Token。
	 *
	 * @param  string  $token  会话 TOKEN。
	 * @return array
	 */
	protected static function parseToken($token)
	{
		$data = YCore::sys_auth($token, 'DECODE');
		$data = explode("\t", $data);
		if (count($data) != 5) {
			YCore::exception(STATUS_LOGIN_TIMEOUT, '登录超时,请重新登录');
		}
		$result = [
            'userid'    => $data[0], // 用户ID。
            'password'  => $data[1], // 加密后的密码。
			'logintime' => $data[2], // 登录时间。
            'platform'  => $data[3], // 登录平台标识。1-IOS|2-Android|3-H5|4-Web。
            'env'       => $data[4], // Token 所属的环境。
        ];
        if ($data[4] != App::getConfig('app.env')) {
            YCore::exception(STATUS_SERVER_ERROR, 'TOKEN 不属于当前环境,请检查请求的接口地址是否有误或旧环境TOKEN缓存未清除');
        }
		return $result;
    }

    /**
     * 创建对外使用的 openid。
     *
     * @param  string  $mobile  注册的手机号。
     * @return void
     */
    private static function makeUserOpenId($mobile)
    {
        $str   = 'abcdefghigklmnopqrstuvwxyz1234567890';
        $shuf  = str_shuffle($str);
        $chars = substr($shuf, 0, 4);
        return md5($mobile . md5($chars));
    }

    /**
     * 检查密码格式。
     * @param  string  $password  密码。
     * @return void
     */
    public static function checkPassword($password)
    {
        $data  = ['password' => $password];
        $rules = [
            'password' => '密码|require|alpha_dash|len:6:20:0'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 检查验证码格式。
     * @param  string  $code  验证码。
     * @return void
     */
    public static function checkCaptcha($code)
    {
        $data  = ['code' => $code];
        $rules = [
            'code' => '验证码|require|alpha_number|len:4:8:0'
        ];
        Validator::valido($data, $rules);
    }


}