<?php
/**
 * 系统友情链接接口。
 * 
 * @author fingerQin
 * @date 2018-09-05
 * @version 1.0.0
 */

namespace Apis\app\v100\System;

use Apis\AbstractApi;
use Services\System\Link;

class SystemLinkApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $result = Link::list();
        $this->render(STATUS_SUCCESS, 'success', ['list' => $result]);
    }
}