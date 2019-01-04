<?php
/**
 * 短信相关队列消息业务封装。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Services\Sms;

use Utils\YCache;
use Utils\YLog;
use Utils\YCore;
use Utils\YInput;
use Models\SmsSendLog;
use Models\SmsConf;
use Services\Sms\Driver\Luosimao;

class Consume extends \Services\Sms\AbstractBase
{
    /**
     * 发送短信(守护进程/常驻后台进程)。
     *
     * @return void
     */
    public static function sendSms()
    {
        // [1]
        $redis    = YCache::getRedisClient();
        $queueKey = self::SMS_QUEUE_KEY;
        $queueIng = $queueKey . '-ing';
        $arrValue = [];
        try {
            // [2] 因进程异常退出导致短信队列消费延迟。则不再进行第二次发送。
            $redis->delete($queueIng);
            $channelConf     = [];
            $SmsSendLogModel = new SmsSendLog();
            $switch          = YCore::appconfig('sms.is_send_sms');
            // [3]
            while (true) {
                $str = $redis->bRPopLPush($queueKey, $queueIng, 60);
                if (!empty($str)) {
                    $arrValue = json_decode($str, true);
                    try {
                        // 项目配置文件当中 sms.switch 判断。
                        if ($switch == 1) {
                            // 防止修改表优先级无效，因此放到循环中
                            $channelConf = self::getSmsChannelConf();
                            self::sendRealSms($arrValue['mobile'], $arrValue['content'], $channelConf);
                            YLog::log("sms::mobile::{$arrValue['mobile']}:{{$arrValue['id']}}:ok", 'sms', 'sys_send_consume');
                        } else {
                            YLog::log("sms:: 短信配置关闭状态,短信不会真实发送", 'sms', 'sys_send_consume');
                        }
                        // 更新短信日志
                        $redis->lRem($queueIng, $str, 1);
                        $updata = [
                            'error_msg'  => '发送成功',
                            'sms_status' => SmsSendLog::SEND_STATUS_SENT,
                            's_time'     => date('Y-m-d H:i:s', time()),
                            'channel_id' => YInput::getInt($channelConf, 'channel_id', 0)
                        ];
                        $SmsSendLogModel->update($updata, ['id' => $arrValue['id']]);
                    } catch (\Throwable $e) {
                        $redis->lRem($queueIng, $str, 1);
                        $log = [
                            'sms'    => $arrValue,
                            'errmsg' => $e->getMessage()
                        ];
                        YLog::log($log, 'sms', 'error');
                        $updata = [
                            'error_msg'  => mb_substr($e->getMessage(), 0, 255, 'UTF-8'),
                            'sms_status' => SmsSendLog::SEND_STATUS_FAILD,
                            's_time'     => date('Y-m-d H:i:s', time()),
                            'channel_id' => YInput::getInt($channelConf, 'channel_id', 0)
                        ];
                        $SmsSendLogModel->update($updata, ['id' => $arrValue['id']]);
                    }
                } else {
                    $SmsSendLogModel->ping();
                    usleep(100000); // 0.1秒。
                }
            }
        } catch (\Throwable $e) {
            $redis->lRem($queueIng, $str, 1); // 短信发送失败将不再发送。将失败信息写入即可。
            // 错误日志
            $errorMsg = "sms::error:Exception Message:" . $e->getMessage();
            $log = [
                'sms'    => $arrValue,
                'errmsg' => $errorMsg
            ];
            YLog::log($log, 'sms', 'error');
            if (!empty($arrValue)) {
                $updateData = [
                    'sms_status' => SmsSendLog::SEND_STATUS_FAILD,
                    's_time'     => date('Y-m-d H:i:s'),
                    'channel_id' => 0,
                    'error_msg'  => mb_substr($errorMsg, 0, 255, 'UTF-8'),
                ];
                $SmsSendLogModel->update($updateData, ['id' => $arrValue['id']]);
            }
            echo $errorMsg . "\n";
        }
    }

    /**
     * 获取当前可用短信通道配置。
     *
     * @return array
     */
    public static function getSmsChannelConf()
    {
        $SmsConfModel = new SmsConf();
        $config = $SmsConfModel->fetchOne([], ['status' => SmsConf::STATUS_YES], 'level ASC');
        if (empty($config)) {
            YCore::exception(STATUS_SERVER_ERROR, '当前无可用短信通道');
        }
        return [
            'channel_id'     => $config['id'],       // 通过ID。
            'channel_name'   => $config['account'],  // 通道账号。
            'channel_pwd'    => $config['passwd'],   // 通道密码。
            'channel_secret' => $config['secret'],   // 通道密钥。
            'channel_key'    => $config['keywords']  // 通道标识 KEY。
        ];
    }

    /**
     * 调用短信通道主程序发送短信
     * 
     * @param  string  $mobile       手机号码。
     * @param  string  $content      短信内容。
     * @param  array   $channelInfo  短信配置。
     *
     * @return void
     */
    public static function sendRealSms($mobile, $content, $channelInfo)
    {
        switch ($channelInfo['channel_key']) {
            case 'luosimao':
                $sendObj = new Luosimao($channelInfo);
                $sendObj->send($mobile, $content);
                break;
            default:
                YCore::exception(STATUS_SERVER_ERROR, '当前通道对应发送程序不支持');
                break;
        }
    }
}