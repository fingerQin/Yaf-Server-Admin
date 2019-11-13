<?php
/**
 * 公告列表接口。
 * 
 * @author fingerQin
 * @date 2019-04-22
 * @version 1.0.0
 */

namespace Apis\app\v100\Notice;

use Apis\AbstractApi;
use Services\System\Notice;

class NoticeListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $page   = $this->getInt('page', 1);
        $result = Notice::lists($page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}