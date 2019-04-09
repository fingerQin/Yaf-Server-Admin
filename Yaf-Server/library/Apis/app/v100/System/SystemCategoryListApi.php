<?php
/**
 * 分类列表接口。
 * 
 * @author fingerQin
 * @date 2019-04-09
 * @version 1.0.0
 */

namespace Apis\app\v100\System;

use Apis\AbstractApi;
use Services\System\Category;

class SystemCategoryListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $parentid = $this->getInt('parentid', 0);
        $catType  = $this->getInt('cat_type', 0);
        $result   = Category::list($parentid, $catType, 1);
        $this->render(STATUS_SUCCESS, 'Success', $result);
    }
}