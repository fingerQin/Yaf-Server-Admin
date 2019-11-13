<?php
/**
 * 文章详情接口。
 * 
 * @author fingerQin
 * @date 2019-08-21
 * @version 1.0.0
 */

namespace Apis\app\v100\News;

use Apis\AbstractApi;
use Services\System\News;

class NewsDetailApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $newsId = $this->getString('news_id', -1);
        $result = News::detail($newsId);
        $this->render(STATUS_SUCCESS, 'success', $result);
    }
}