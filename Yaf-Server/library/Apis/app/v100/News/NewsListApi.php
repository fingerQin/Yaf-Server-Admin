<?php
/**
 * 文章列表接口。
 * 
 * @author fingerQin
 * @date 2019-08-21
 * @version 1.0.0
 */

namespace Apis\app\v100\News;

use Apis\AbstractApi;
use Services\System\News;

class NewsListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $catId  = $this->getString('cat_id', -1);
        $page   = $this->getInt('page', 1);
        $result = News::lists($catId, $page, 20);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}