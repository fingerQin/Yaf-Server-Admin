<?php
/**
 * 短信管理。
 * @author fingerQin
 * @date 2018-07-07
 */

use Utils\YCore;
use Services\Sms\Sms;

class SmsController extends \Common\controllers\Admin
{
    /**
     * 系统短息列表
     * @author 7015
     */
    public function logAction()
    {
        $keywords   = $this->getString('keywords', '', true);
        $page       = $this->getString('page', 1);
        $where      = [];
        if (!empty($keywords)) {
            $where['a.mobile'] = ['=',$keywords];  // 产品说是匹配内容   现改为手机号
        }
        $search = [
            'keywords' => $keywords
        ];
        $result    = SmsService::getSmsLogs($where, $page,$this->pageSize);
        $paginator = new Paginator($result['total'], $this->pageSize, $search);
        $page_html = $paginator->backendPageShow();
        $this->assign('page_html', $page_html);
        $this->assign('search', $search);
        $this->assign('list', $result['list']);
    }
}