<?php
/**
 * 配置管理。
 * @author fingerQin
 * @date 2016-01-14
 */

use finger\Paginator;
use Services\System\Config;

class ConfigController extends \Common\controllers\Admin
{
    /**
     * 配置列表。
     */
    public function indexAction()
    {
        $keywords  = $this->getString('keywords', '');
        $page      = $this->getInt('page', 1);
        $list      = Config::list($keywords, $page, 20);
        $paginator = new Paginator($list['total'], 20);
        $pageHtml  = $paginator->backendPageShow();
        $this->assign('page_html', $pageHtml);
        $this->assign('keywords', $keywords);
        $this->assign('list', $list['list']);
    }

    /**
     * 添加配置。
     */
    public function addAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $title       = $this->getString('title');
            $cfgKey      = $this->getString('cfg_key');
            $cfgValue    = $this->getString('cfg_value');
            $description = $this->getString('description');
            Config::add($this->adminId, $title, $cfgKey, $cfgValue, $description);
            $this->json(true, '添加成功');
        }
    }

    /**
     * 配置编辑。
     */
    public function editAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $configId    = $this->getInt('configid');
            $title       = $this->getString('title');
            $cfgKey      = $this->getString('cfg_key');
            $cfgValue    = $this->getString('cfg_value');
            $description = $this->getString('description');
            Config::edit($this->adminId, $configId, $title, $cfgKey, $cfgValue, $description);
            $this->json(true, '修改成功');
        } else {
            $configId = $this->getInt('config_id');
            $detail   = Config::detail($configId);
            $this->assign('detail', $detail);
        }
    }

    /**
     * 配置删除。
     */
    public function deleteAction()
    {
        $configId = $this->getInt('config_id');
        Config::delete($this->adminId, $configId);
        $this->json(true, '删除成功');
    }

    /**
     * 清除配置缓存。
     */
    public function clearCacheAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            Config::clearCache();
            $this->json(true, '配置缓存清除成功');
        }
    }
}