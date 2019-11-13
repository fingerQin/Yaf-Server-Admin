<?php
/**
 * Cli 模式专用 Controller。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common\controllers;

class Cli extends Common
{
    /**
     * 重写父方法, Cli 模式关闭模板渲染。
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->end();
        if (PHP_SAPI != 'cli') { // 非 CLI 模式运行则报错。
            \finger\Utils\YCore::exception(STATUS_SERVER_ERROR, '服务器异常,请稍候重试');
        }
    }
}