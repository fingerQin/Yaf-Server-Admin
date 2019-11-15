<?php
/**
 * 系统 Redis 管理。
 * @author fingerQin
 * @date 2019-06-03
 */

use Services\System\RedisManage;

class RedisController extends \Common\controllers\Admin
{
    /**
     * 列表。
     */
    public function listsAction()
    {
        $this->assign('redisKeys', RedisManage::$redisKeys);
    }

    /**
     * Redis 删除。
     */
    public function deleteAction()
    {
        $key = $this->getString('redis_key', '');
        RedisManage::delete($key);
        $this->json(true, '删除成功');
    }
}