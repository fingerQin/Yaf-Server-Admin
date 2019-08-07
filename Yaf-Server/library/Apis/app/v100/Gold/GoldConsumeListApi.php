<?php
/**
 * 用户金币消费记录接口(近3个月的消费记录)。
 * @author fingerQin
 * @date 2018-08-24
 * @version 1.0.0
 */

namespace Apis\app\v100\Gold;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\Gold\Gold;

class GoldConsumeListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $startTime = date('Y-m-d 00:00:00', strtotime('-3month'));
        $page      = $this->getInt('page', 1);
        $token     = $this->getString('token', '');
        $userinfo  = Auth::checkAuth($token);
        $result    = Gold::records($userinfo['userid'], -1, $startTime, '', $page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}