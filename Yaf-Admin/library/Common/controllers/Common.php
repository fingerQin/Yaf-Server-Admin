<?php
/**
 * 公共controller。
 * --1、Yaf 框架会根据特有的类名后缀(Model、Controller、Plugin)进行自动加载。为避免这种情况请不要以这样的类名结尾。
 * --2、鉴于第一点，在 Yaf 框架内的所有类的加载请不要出现 Model、Controller、Plugin 等词出现在类名中。
 * --3、通过 Composer 加载的第三方包不受此影响。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common\controllers;

use finger\Core;
use finger\Validator;

class Common extends \Yaf_Controller_Abstract
{
    /**
     * 请求对象。
     *
     * @var \Yaf_Request_Http
     */
    protected $_request = null;

    /**
     * 视图对象。
     *
     * @var \Yaf_View_Simple
     */
    protected $_view = null;

    /**
     * 当前访问的操作方法名。
     *
     * @var string
     */
    protected $_actionName = '';
    
    /**
     * 当前访问的的控制器名。
     *
     * @var string
     */
    protected $_ctrlName   = '';

    /**
     * 当前访问的模块名。
     *
     * @var string
     */
    protected $_moduleName = '';

    /**
     * 该方法在所有Action执行之前执行。主要做一些初始化工作。
     */
    protected function init()
    {
        $this->_view       = $this->getView();
        $this->_request    = $this->getRequest();
        $this->_actionName = $this->_request->getActionName();
        $this->_moduleName = $this->_request->getModuleName();
        $this->_ctrlName   = $this->_request->getControllerName();
    }

    /**
     * 从请求中读取一个整型数值。
     * -- 1、如果该数据本身不是一个整型，将会抛异常。
     * -- 2、如果该数值不存在将会返回默认值。
     * -- 3、默认值也必须是整型。
     * -- 4、读取的值将从GPC(GET、POST)中读取。
     *
     * @param  string  $name
     * @param  int     $defaultValue
     */
    final protected function getInt($name, $defaultValue = null)
    {
        $gpValue = $this->getGP($name);
        if (is_null($gpValue)) {
            if (is_null($defaultValue)) {
                Core::exception(STATUS_ERROR, "{$name}值异常");
            } else if (!Validator::is_integer($defaultValue)) {
                Core::exception(STATUS_ERROR, "{$name}默认值不是整型");
            } else {
                return $defaultValue;
            }
        } else {
            if (!Validator::is_integer($gpValue)) {
                Core::exception(STATUS_ERROR, "{$name}值不是整型");
            } else {
                return $gpValue;
            }
        }
    }

    /**
     * 从请求中读取一个数组。
     * -- 1、如果该数据本身不是一个数组类型，将会抛异常。
     * -- 2、如果该数值不存在将会返回默认值。
     * -- 3、默认值也必须是数组类型。
     * -- 4、读取的值将从GPC(GET、POST)中读取。
     *
     * @param string $name
     * @param array  $defaultValue
     */
    final protected function getArray($name, $defaultValue = null)
    {
        $gpValue = $this->getGP($name);
        if (is_null($gpValue)) {
            if (is_null($defaultValue)) {
                Core::exception(STATUS_ERROR, "{$name}值异常");
            } else if (!is_array($defaultValue)) {
                Core::exception(STATUS_ERROR, "defaultValue参数不是数组");
            } else {
                return $defaultValue;
            }
        } else {
            if (!is_array($gpValue)) {
                Core::exception(STATUS_ERROR, "{$name}值不是数组");
            } else {
                return $gpValue;
            }
        }
    }

    /**
     * 从请求中读取一个浮点型数值。
     * -- 1、如果该数据本身不是一个浮点型，将会抛异常。
     * -- 2、如果该数值不存在将会返回默认值。
     * -- 3、默认值也必须是浮点型。
     * -- 4、读取的值将从GPC(GET、POST)中读取。
     *
     * @param string $name
     * @param int    $defaultValue
     */
    final protected function getFloat($name, $defaultValue = null)
    {
        $gpValue = $this->getGP($name);
        if (is_null($gpValue)) {
            if (is_null($defaultValue)) {
                Core::exception(STATUS_ERROR, "{$name}值异常");
            } else if (!Validator::is_float($defaultValue)) {
                Core::exception(STATUS_ERROR, "defaultValue参数不是浮点型");
            } else {
                return $defaultValue;
            }
        } else {
            if (!Validator::is_float($gpValue)) {
                Core::exception(STATUS_ERROR, "{$name}值不是浮点型");
            } else {
                return $gpValue;
            }
        }
    }

    /**
     * 从请求中读取一个字符串数值。
     * -- 1、如果该数值不存在将会返回默认值。
     * -- 2、读取的值将从GPC(GET、POST)中读取。
     * -- 3、数据会进行防注入处理。
     *
     * @param string $name
     * @param int    $defaultValue
     */
    final protected function getString($name, $defaultValue = null)
    {
        $gpValue = $this->getGP($name);
        if (is_null($gpValue)) {
            if (is_null($defaultValue)) {
                Core::exception(STATUS_ERROR, "{$name}值异常");
            } else {
                return $defaultValue;
            }
        } else {
            return $gpValue;
        }
    }

    /**
     * 获取GET、POST、路由里面的值。
     * -- 1、先读路由分解出来的参数、再读GET、其次读POST。
     *
     * @param  string $name
     * @return mixed
     */
    final protected function getGP($name)
    {
        $value = $this->_request->getParam($name);
        if (is_array($value) && ! empty($value)) {
            return $value;
        }
        if (strlen($value) > 0) {
            return $value;
        }
        if (isset($_GET[$name])) {
            return $this->_request->getQuery($name);
        } else if (isset($_POST[$name])) {
            return $this->_request->getPost($name);
        } else {
            return null;
        }
    }

    /**
     * 关闭模板渲染。
     */
    protected function end()
    {
        \Yaf_Dispatcher::getInstance()->autoRender(false);
    }

    /**
     * 模板传值(this->_view->assign())。
     *
     * -- 该方法是封装了 Yaf_View 提供的 assign() 方法。
     * 
     * @param  mixed  $name  字符串或者关联数组, 如果为字符串, 则$value不能为空, 此字符串代表要分配的变量名. 如果为数组, 则$value须为空, 此参数为变量名和值的关联数组.
     * @param  mixed  $value 分配的模板变量值
     * @return bool
     */
    protected function assign($name, $value = null)
    {
        return $this->_view->assign($name, $value);
    }

    /**
     * 输出JSON到浏览器。
     *
     * @param  string $message   提示信息。
     * @param  array  $data      返回的数据。如果不存在则连data键不会返回。
     * @return void
     */
    protected function successJson($message, array $data = null)
    {
        $this->json(true, $message, $data);
    }

    /**
     * 输出JSON到浏览器。
     *
     * @param  boolean $status   操作成功与否。true:成功、false：失败。
     * @param  string  $message  提示信息。
     * @param  array   $data     返回的数据。如果不存在则连data键不会返回。
     * @return void
     */
    protected function json($status, $message, array $data = null)
    {
        $result = [
            'msg' => $message
        ];
        if ($status) {
            $result['code'] = STATUS_SUCCESS;
        } else {
            $result['code'] = STATUS_ERROR;
        }
        if (!is_null($data)) {
            $result['data'] = $data;
        }
        echo json_encode($result);
        $this->end();
        exit();
    }

    /**
     * 错误信息。
     *
     * @param  string  $message   错误信息。
     * @param  string  $url       跳转地址。
     * @param  int     $second    跳转时间。
     */
    protected function error($message = '', $url = '', $second = 3)
    {
        $this->assign('message', $message);
        $this->assign('url', $url);
        $this->assign('second', $second);
        $scriptPath = $this->getViewPath();
        $this->_view->display($scriptPath[0] . "/common/error.php");
        $this->end();
    }

    /**
     * 去登录的错误信息提示。
     *
     * @param  string  $message   错误信息。
     * @param  string  $url       跳转地址。
     * @param  int     $second    跳转时间。
     */
    protected function loginTips($message, $url)
    {
        $this->assign('message', $message);
        $this->assign('url', $url);
        $scriptPath = $this->getViewPath();
        $this->_view->display($scriptPath[0] . "/common/loginTips.php");
        $this->end();
    }

    /**
     * 成功信息。
     *
     * @param  string  $message     错误信息。
     * @param  string  $url         跳转地址。
     * @param  int     $second      跳转时间。
     * @return void
     */
    protected function success($message = '', $url = '', $second = 3)
    {
        $this->assign('message', $message);
        $this->assign('url', $url);
        $this->assign('second', $second);
        $scriptPath = $this->getViewPath();
        $this->_view->display($scriptPath[0] . "/common/error.php");
        $this->end();
    }
}