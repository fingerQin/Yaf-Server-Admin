<?php
/**
 * Cli 模式专用 Controller。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common\controllers;

use finger\App;
use finger\Core;

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
        if (App::isCli()) { // 非 CLI 模式运行则报错。
            Core::exception(STATUS_SERVER_ERROR, '不是 Cli 模式');
        }
    }
}