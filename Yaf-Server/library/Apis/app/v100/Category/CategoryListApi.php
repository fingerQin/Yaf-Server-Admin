<?php
/**
 * 分类列表接口。
 * 
 * @author fingerQin
 * @date 2019-04-09
 * @version 1.0.0
 */

namespace Apis\app\v100\Category;

use Apis\AbstractApi;
use Services\System\Category;

class CategoryListApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $parentid  = $this->getInt('parentid', 0);
        $catType   = $this->getInt('cat_type', 0);
        $addressId = Category::list($parentid, $catType, 1);
        $this->render(STATUS_SUCCESS, '添加成功', ['address_id' => $addressId]);
    }
}