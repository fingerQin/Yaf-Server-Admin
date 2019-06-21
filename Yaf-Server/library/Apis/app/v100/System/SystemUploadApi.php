<?php
/**
 * 系统上传接口。
 * @author fingerQin
 * @date 2018-06-27
 * @version 1.0.0
 */

namespace Apis\app\v100\System;

use Apis\AbstractApi;
use Services\User\Auth;
use Services\System\Upload;

class SystemUploadApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @see Api::runService()
     * @return void
     */
    protected function runService()
    {
        $token    = $this->getString('token', '');
        $userinfo = Auth::checkAuth($token);
        $result   = Upload::uploadImage(2, $userinfo['userid'], 'images', 0.5, 'image');
        $this->render(STATUS_SUCCESS, '上传成功', $result);
    }
}