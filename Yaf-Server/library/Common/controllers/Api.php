<?php
/**
 * API 接口专用 controller。
 * --1、Yaf 框架会根据特有的类名后缀(Model、Controller、Plugin)进行自动加载。为避免这种情况请不要以这样的类名结尾。
 * --2、鉴于第一点，在 Yaf 框架内的所有类的加载请不要出现 Model、Controller、Plugin 等词出现在类名中。
 * --3、通过 Composer 加载的第三方包不受此影响。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common\controllers;

class Api extends Common
{

}