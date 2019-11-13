[TOC]

## Yaf-Server 使用文档##

### 1 前言

>   Yaf-Server 是一个基于 Yaf 框架实现的通用型服务端项目开发框架。将一些常见业务场景已经实现。从而达到快速开发不同业务项目的目的。所谓通用型业务场景包含：登录、注册、短信发送、APP 消息推送、日志存储、文件上传、API 接口、管理后台等。该文档旨在说明这些通用业务的设计思路，以及底层封装的通用方法的使用。让团队成员快速融入到项目的开发及维护当中。
>
>   Yaf-Server 只提供 API 接口、内嵌页(APP调用)、命令行（Cli）访问。为什么不提供 PC版、手机触屏版是因为它们的会话需要 SESSION 机制，以及我们采用了前端团队采用 Nodejs 来渲染的机制。所以，我们只安安心心做接口提供服务就好。管理后台在单独的项目 Yaf-Admin 内实现。

### 2 框架选型

#### 2.1 为什么选择 Yaf 框架

> 目前关于 PHP 的开发框架非常多。有 Laravel、YII、Phalcon 等。我们为什么会选择 Yaf 框架呢？要回答这个问题，我们要从性能、简洁、易用性三个方面进行分析。

#### 2.2 性能

> 从目前 PHP 相关社区评测数据得知，框架的性能排序(优 -> 次)：Yaf > Phalcon > Laravel > YII。
>
> 所以，从框架性能程度上我们有选择 Yaf 框架的理由。这里我要补充一个小点。Yaf 框架之所以快，除了主要是因为它是用 C 语言编写的扩展外，另外一个原因是它本身只实现了基本的 MVC 构架。其次，像 Laravel 和 YII 等用 PHP 编写的框架，它们不仅实现了 MVC，还有缓存、DB 等封装。加上成熟的 PHP 框架在不开启 opcache 的情况下，通过入口文件初始化加载时会引导很多文件，当然开发过程中开发机器稍微差一点会感觉到明显的请时响应缓慢。

#### 2.3 简洁

> 一个优秀的框架越简洁就越容易上手使用。通过对这些框架的使用经验得知，框架的简洁排序(优 > 次)：Yaf > Laravel > Phalcon > YII。
>
> 所以，从框架简洁程度上我们有选择 Yaf 框架的理由。Laravel 框架虽然简洁，但是在一些高级主题方面会让你看了文档也不明白其所以然。不得不去看源码。曾有人说：Laravel 是给高级开发者用的。这话我不是特别认同。从设计原则来讲，一款流行的框架，除了在设计上面做到优雅简洁安全稳定之后，还要在文档上能有所体现。不能以是给高级开发者用的而一言蔽之。

#### 2.4 易用性

> 易用性是一个比较主观的感受。一般来说，一个优秀的框架，越简洁优雅就越容易使用。目前公认的易用性排序(优 > 次)：Yaf > Laravel > Phalcon > YII。
>
> 所以，从框架易用性程度上我们有选择 Yaf 框架的理由。

#### 2.5 Yaf 框架的缺点

> 任何一款框架都有自身的特点和缺点。由于 Yaf 框架只实现了基础的 MVC 构架。以及它脱胎于鸟哥当年在新浪微博时所编写。局限于时代它的优点和缺点一样的明显。优点是在微博这种大型应用下经受了考验。缺点是不能满足一些前沿的设计。主要包含如下缺点：

1. 不支持 RESTFul 。
2. 未实现 ORM。
3. 未实现一套专用的视图引擎。默认是使用 PHP 原生语法来实现。
4. 运维不部署。

- 不支持 RESTFul 我觉得并不会影响项目的使用。我们可以采用其他方式的风格提供接口。
- 未实现 ORM。开源的 ORM 非常多。自己编写一套简单易用的 MySQL 操作也很简单。
- 视图引擎可以使用成熟的模板引擎：Smarty、Twig 等。Yaf 框架都是可以支持的。



### 3 Yaf 框架安装

Yaf 框架文档：http://www.laruence.com/manual/index.html

我们的基建项目采用了 PHP 7 才有的语法。所以，请使用 PHP 7.1 以上的版本。

```
$ wget http://pecl.php.net/get/yaf-3.0.7.tgz
$ tar zxvf yaf-3.0.7.tgz
$ cd yaf-3.0.7
$ phpize
$ make && make install
```

然后，在 php.ini 配置文件末尾增加如下代码行：

```
extension = yaf.so
yaf.use_spl_autoload = 1
```

> yaf.use_spl_autoload = 1 的意思是允许我们加载第三方的开发包。比如我们 PHP 经常用的 Composer 包。

配置 `OK` 之后记录重启你的 `PHP-FPM` 。



> 注：除了 Yaf 扩展，我们的基建项目还用到了 Redis、MongoDB 扩展。



### 4 架构

#### 4.1 目录结构

```
.
├── apps						   应用目录
│   ├── Bootstrap.php			 	项目启动文件。每次请求都会自动加载。
│   ├── controllers					默认的模块（Index）的控制器目录。
│   ├── modules					    自定义模块的控制器目录。			 
│   └── views					    默认的模块（Index）的 View 层目录。
├── config						    应用配置目录。
│   ├── config.ini					 应用配置文件。
│   └── constants.php				 应用常量定义文件。所有的全局常量这里定义。
├── docs							应用文档存放位置。
│   └── Yaf-Server使用文档.md
├── library							自定义类库的目录。Model、Service、工具类。
│   ├── Apis						API 接口在这里。
│   ├── Common						公共的 Controller、Plugins 在这里。
│   ├── finger						自定义的类库。DB、Cookie、Cache、Session、Log 等。
│   ├── Models						应用 Model 存放目录。
│   ├── PHPExcel					PHPExcel 库。Excel 导入导出需要。
│   ├── Services					应用服务层（Service）存放目录。
│   ├── Threads						多进程的调用全部定义在这里。然后再在 Cli 命令行调用它。
│   └── Utils						工具类。核心方法、字符串、日期、目录、文件等。
├── logs							日志存放目录。
│   ├── accessLog					应用被请求的日志存放这里。前期上线会写入日志。便于查看监控。
│   ├── apis						API 被请求的日志存放这里。
│   ├── serviceErr					服务层（Services）中任何业务异常的日志存放这里。
│   ├── sms							短信日志存放目录。
│   ├── sql							SQL 存放这里。开发环境需要。
│   └── unknownErr					未知的 PHP 错误存放目录。如：Notice、Warning、Fatal。
├── public							应用入口目录。
│   ├── cli.php						应用命令行入口文件。
│   ├── index.php					应用 Web 入口文件。
│   ├── statics						应用静态文件（Css、Js、Image、Font）存放这里。也可以放 OSS。
│   └── uploads						应用上传文件存放目录。也可以放 OSS。
└── README.md
```

#### 4.2 生命周期

> 本小节我们对 Yaf 框架结合项目之后的生命周期进行讲解。

##### ![](G:\codespace\myself\Yaf-Server\docs\images\yaf_sequence.png)



以下流程图是 Yaf 文档里面的。我们在此基础上做了小小的调整。

1) 入口文件启动。并且加载常量文件 `config/constants.php` 文件。

2) 启动应用。通过调用 `Yaf_Application` 类。并把配置文件路径当作参数传入。

3) 如果应用调用了 `bootstrap()` 方法，则会加载 `apps` 目录下的 `Bootstrap.php` 文件。该文件必须继承 `Yaf_Bootstrap_Abstract` 类。如果没有调用则不会加载。如果加载了，那么在 `Bootstrap.php` 中按规则定义的方法将会被自动依次调用。然后再调用 `run` 方法启动应用。

4) 加载 `Yaf` 框架路由。

5) 调用 `Controller` 控制器及 `Action` 与 `View` 视图。

6) 输出结果。应用执行结束。



#### 4.3 入口文件

```php
define('MICROTIME', microtime());
define('APP_PATH', dirname(dirname(__FILE__)));
require(APP_PATH . '/vendor/autoload.php');
require(APP_PATH . '/config/constants.php');
error_reporting(0);
$app = new \Yaf_Application(APP_PATH . '/config/config.ini', 'conf');
$app->bootstrap()->run();
```

通过生命周期小节，我们已然知道这几行代码都干了什么好事儿。



> 注：请一定要通过 Composer 安装依赖的第三方包。请一定要通过 Composer 安装依赖的第三方包。请一定要通过 Composer 安装依赖的第三方包。重要的事情说三遍。否则，入口文件在加载 `/vendor/autoload.php` 文件时会报错。



#### 4.4 重写规则

**1) Apache 重写规则:**

```ini
#.htaccess, 当然也可以写在httpd.conf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* index.php
```



**2) Nginx 重写规则:**

在 server 里面增加如下：

```nginx
if (!-e $request_filename) {
    rewrite ^/(.*)  /index.php/$1 last;
}
```



#### 4.5 配置文件

`Yaf` 框架本身并未定死配置的目录位置。你可以放在任何位置。只需要在启动应用的时候将配置文件路径传递给它即可。而我们这套基建项目，将配置文件放在了 `apps/config/config.php` 。

在该文件当中我们定义了很多配置。然而只有了了几个配置是与 `Yaf` 框架相关的。本小节只介绍 `Yaf` 框架相关的几个配置。在其他小节再讲与 `Yaf` 框架不相关的配置。

**1) application.directory**

该配置是 `Yaf` 框架必须的的配置。指定我们的应用（`Controller`、`Model`、`Views`、`Modules`）的位置。绝对路径地址。

**2) application.library**

指定本地类库的位置。绝对路径地址。

**3) application.view.ext**

指定 View 模板的扩展名。

**4) application.modules**

指定我们当前有哪些模块。如果新建的模块不在这里定义。那么任何到该模块的请求都不会生效。非常重要。

**5）application.dispatcher.defaultModule**

指定默认的模块。如果不配置，Yaf 框架默认为 Index。

**6）application.dispatcher.defaultController**

指定默认的控制器。如果不配置，Yaf 框架默认为 Index。

**7）application.dispatcher.defaultAction**

指定默认的操作。如果不配置，Yaf 框架默认为 Index。

**8）application.dispatcher.throwException**

指定 Yaf 框架在出错时，是否抛出异常。在开发环境，这是必须的。

**9）application.dispatcher.catchException**

如果开启, 在有未捕获的异常的时候,控制权会交给 ErrorController 的 errorAction 方法。在我们的基建项目当中，这个必须开启。因为，我们需要通过这个特性来将应用当中所有的业务异常全部在这里进行集中管理。以及根据不同的异常写入日志。



> 关于 Yaf 框架自身的配置文件就讲完了。我们现在讲几个跟具体功能无关的应用顶层配置。



**10）app.env**

指定当前应用的运行环境。dev-开发环境、pre-预发布环境、beta-公测环境、pro-正式环境。我们在开发环境会关闭短信发送等操作。这个值非常有用。

**11）app.debug**

设置为 true 时，则任何 PHP 抛出的错误，都会输出到客户端/浏览器。当然，必须 `error.display.errors` 设置为 1 的情况下。

**12）app.timezone**

设置应用时区。

**13）app.key**

应用密钥。这个很重要。在一些通用的加密环境，它起了决定性作用。



#### 4.6 自动加载

自动加载是任何框架都必须明确并必知的关键点。

关于自动加载可以直接阅读 Yaf 的文档：http://www.laruence.com/manual/yaf.autoloader.html

**自定义类的类名不要出现以 Controller、Plugin、Model 关键词命令。就算要存在也不要出现在类名的后面。**

注：关于自动加载要注意的是配置文件 `yaf.name_suffix` 。文档位置：http://www.laruence.com/manual/yaf.ini.html 。它的默认值为 1。也就是说，我们定义的 Controller、Action、Plugin、Model 类的时候。必须在类名后面追加 Controller、Action、Plugin、Model。同时 Yaf 框架有一个特点，当我们在 PHP 脚本中调用带这些后缀的类的时候，Yaf 框架会去 apps 目录下面去寻找。所以， 我就把 Model 单独拎出来。避免，每个 Model 后面增加一个 Model 后缀。



>  因为我们已经打开了 yaf.use_spl_autoload 配置。所以，通过 composer 加载的第三方包里面的类不受 Controller、Model、Plugin 影响。



### 5 路由

路由文档：http://www.laruence.com/manual/yaf.routes.html

**关于 Yaf 框架的路由要注意：路由注册的顺序很重要, 最后注册的路由协议, 最先尝试路由, 这就有个陷阱。**

默认的路由是第一个值代表 Module，第二个值代表 Controller，第三个值代表 Action。

当路径当中只存在两个值的时候，第一个值代表 Controller，第二个值代表 Action。

自定义路由在应用启动文件 Bootstrap.php 中的 Bootstrap 类中方法内添加。

```
<?php

class Bootstrap extends Yaf_Bootstrap_Abstract
{
    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        // 添加配置中的路由
        $router->addConfig(Yaf_Registry::get("config")->routes);
    }
}
```



### 6 日志

日志是一个系统当中最重要的组成部分。它不仅可以分析用户的行为，也可以帮助我们排查问题。以及监控我们的系统健康状况。

#### 6.1 日志收集

当我们在配置文件 `apps/config/config.php` 中将 `application.dispatcher.catchException` 设置为 1 时，整个应用中所有的异常都会交由对应模块下的 `ErrorController` 的 `errorAction ` 方法处理。那么，我们就可以在整个应用当中任何位置抛出异常。然后在 `errorAction` 方法当中记录日志。便于我们排查问题。

由于默认的异常只会记录异常的堆栈路径。在具体位置运行的方法以及方法参数并不会提供。这就导致我们在排查重现问题的时候就非常的困难。于是，我在框架当中自定义了一个业务异常类：`ServiceException`。

日志类似如下格式：

```
ErrorTime:2018-07-04 15:35:04
ErrorLog:Error Message: 您的APP太旧请升级! 
Error Code: [503] 
Class Name and Method: libs\apis\Factory::factory
Method Params:
Array
(
    [0] => Array
        (
            [appid] => jinr_ios_app
            [timestamp] => 1530689704906
            [channel] => 360
            [longitude] => 
            [latitude] => 
            [app_v] => 1.1.0
            [type] => 2
            [device_token] => d8c8579a53f16d479f5ab2b3860f107efe7214f09593d61b522c358332c721ce
            [unique_id] => A614133B-4FE4-4380-A22B-FC3AC1EB2D76
            [platform] => 1
            [token] => 
            [v] => 1.0.0
            [method] => system.init
            [oriJson] => {"appid":"jinr_ios_app","timestamp":"1530689704906","channel":"360","longitude":"","latitude":"","app_v":"1.1.0","type":2,"device_token":"d8c8579a53f16d479f5ab2b3860f107efe7214f09593d61b522c358332c721ce","unique_id":"A614133B-4FE4-4380-A22B-FC3AC1EB2D76","platform":"1","token":"","v":"1.0.0","method":"system.init"}
            [sign] => 484FC4265CC1F21C206CFC18A0ACFA4E
            [_userid] => 0
            [_ip] => 61.141.64.186
            [_datetime] => 2018-07-04 15:35:04
        )
)

Stack trace:
#0 /home/wwwroot/xgfrontend/libs/apis/Factory.php(58): libs\Utils\YCore::exception(503, '\xE6\x82\xA8\xE7\x9A\x84APP\xE5\xA4\xAA\xE6\x97\xA7...')
#1 /home/wwwroot/xgfrontend/wwwroot/api/application/index/Index.php(44): libs\apis\Factory::factory(Array)
#2 [internal function]: app\index\Index->index()
#3 /home/wwwroot/xgfrontend/thinkphp/library/think/App.php(223): ReflectionMethod->invokeArgs(Object(app\index\Index), Array)
#4 /home/wwwroot/xgfrontend/thinkphp/library/think/App.php(388): think\App::invokeMethod(Array, Array)
#5 /home/wwwroot/xgfrontend/thinkphp/library/think/App.php(129): think\App::module(Array, Array, NULL)
#6 /home/wwwroot/xgfrontend/thinkphp/start.php(18): think\App::run()
#7 /home/wwwroot/xgfrontend/wwwroot/api/index.php(33): require('/home/wwwroot/x...')
#8 {main}
```

通过如上示例日志，我们可以知道。我们记录了如下信息：

- 日志时间
- 异常 Message。
- 异常 Code。
- 异常发生时所在位置的方法。
- 异常发生时所在位置的方法参数。
- 异常发生时的堆栈信息。



#### 6.2 日志存储

通过 4.1 小节的目录结构，我们已经知道如下信息：

```
├── logs							日志存放目录。
│   ├── accessLog					 应用被请求的日志存放这里。前期上线会写入日志。便于查看监控。
│   ├── apis						API 被请求的日志存放这里。
│   ├── serviceErr					 服务层（Services）中任何业务异常的日志存放这里。
│   ├── sms							短信日志存放目录。
│   ├── sql							SQL 存放这里。开发环境需要。
│   └── errors					     未知的 PHP 错误存放目录。如：Notice、Warning、Fatal。
```

我们根据不同功能作用将日志按照了不同的目录进行了区分。并且，在目录下面的日志全部按照天为单位进行切割。

除了这些系统固定会产生的日志之外，我们还会在不同的模块及不同业务中打印业务相关的日志。比如：支付。

那么，我们要怎样才能实现将日志输出到自定义的目录呢？我们通过 `Utils\YLog::log()` 方法进行日志输出。具体的方法使用介绍请查看该方法的方法注释。

```php
$ip         = YCore::ip();
$url        = YUrl::getUrl();
$postParams = $request->getPost();
YLog::log(['ip' => $ip, 'url' => $url, 'params' => $postParams], 'accessLog', 'log');
```



#### 6.3 MongoDB 存储

我们每次调用 `Utils\YLog::log()` 方法的时候，都会向指定的 `Redis` 队列写入这些日志数据。然后，通过命令行`php cli.php Log/writeMongDb ` 启动常驻进程写入 `MongoDB`。



### 7 异常

#### 7.1 Yaf 内置异常

文档：http://www.laruence.com/manual/yaf.class.exception.html

```
Yaf_exception
Yaf_Exception_StartupError
Yaf_Exception_RouterFailed
Yaf_Exception_DispatchFailed
Yaf_Exception_LoadFailed
Yaf_Exception_LoadFailed_Module
Yaf_Exception_LoadFailed_Controller
Yaf_Exception_LoadFailed_Action
Yaf_Exception_LoadFailed_View
Yaf_Exception_TypeError
```

#### 7.2 自定义异常

我们只自定义了一个异常：`ServiceException` 。它的位置在 `library/finger/ServiceException.php` 。

该异常是如何生效的？

我们在应用启动文件 `Bootstrap.php` 当中设置了如下：

```php
......
/**
 * 错误相关操作初始化。
 * -- 1、开/关 PHP 错误。
 * -- 2、接管 PHP 错误。
 */
public function _initError()
{
	$config = \Yaf_Registry::get('config');
	ini_set('display_errors', $config->get('error.display.errors'));
	set_error_handler(['\Utils\YCore', 'errorHandler']);
	register_shutdown_function(['\Utils\YCore', 'registerShutdownFunction']);
}
......
```

通过以上代码，我们可以知道，在应用启动中，我们调用了 `set_error_handler`、`register_shutdown_function` 方法调用了 `\Utils\YCore::errorhandler` 和 `\Utils\YCore::registerShutdownFunction` 方法。以此来实现当 `PHP` 当中有任何错误的时候，调用 `YCore::exception` 方法抛出自定义异常。当然，直接调用该方法也可以抛出自定义异常。

### 8 数据库

> `Yaf` 框架只实现了基本的 `MVC` 架构。所以，对数据库的操作必须自己实现。因其开源的 `ORM` 太笨重，学习成本太高。于是，我们自己实现了一个非常简单易用的针对 `MySQL` 的 `ORM`。

#### 8.1 配置文件

在配置文件 `config.ini` 当中，我们有如下配置：

```ini
; MySQL 配置
mysql.default.host    = 127.0.0.1
mysql.default.port    = 3306
mysql.default.user    = admin
mysql.default.pwd     = f6bea7c6f222831c658d89e49930d936
mysql.default.dbname  = www.itfangtan.com
mysql.default.charset = utf8
```

`mysql.default.*` 中的 `default` 指的是默认的数据库。当我们有多个数据库需要连接的时候，可以增加如下配置：

```ini
; MySQL 配置
mysql.activity.host    = 127.0.0.1
mysql.activity.port    = 3306
mysql.activity.user    = admin
mysql.activity.pwd     = f6bea7c6f222831c658d89e49930d936
mysql.activity.dbname  = www.itfangtan.com
mysql.activity.charset = utf8
```

在 `library/finger/Database/Connection.php` 中有一个 `connection` 方法，默认使用 `default` 连接数据库。当我们需要指定其他数据库配置连接的时候，只需要在创建 `Model` 的时候，指定当前 `Model` 由哪个库来操作。

```php
namespace Models;

class ApiAuth extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName = 'finger_api_auth';

    protected $primaryKey = 'id';
    
    protected $dbOption = 'activity';
}
```

当然，我们也可以直接在 `new Model` 的时候指定。但是，这种方式不够灵活。

```
$UserModel = new User('activity');
```

如果我们在使用数据库原生 SQL 操作的方式，此时我们没办法使用 Model 这样的快捷指定的方式。可以通过如下方式实现：

```php
Db::query($sql, $params, true, 'activity');
```

其中第四个参数指的是配置文件 `config.ini` 中 `mysql.xxx.host` 当中的 `xxx` 部分。



#### 8.2 创建 Model

我们在 `library\Models` 目录下创建了一个 `AbstractBase` 类。该类继承了 `finger\Database\Models` 类。我们所有的 `Model` 必须继承 `AbstractBase` 类。

```php
<?php
namespace Models;

class User extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'finger_user';

    protected $primaryKey = 'userid';
}
```

我们定义了 `finger_user` 表对应的 Model。并且设置了表的主键。关于这些受保护的属性以后以下各类。

##### 8.2.1 $tableName

指定当前 Model 的表名。该属性必须设置。记住，表名是实际的表名。包含前缀也必须设置。

##### 8.2.1 $primaryKey

指定当前表的主键字段名。该属性可以不设置。默认为 id。不过目前该属性未使用。后续可能会增加对此值的利用。

##### 8.2.2 $createTime

指定当前表中创建日期的字段名。我们在 `AbstractBase` 类中设置成了 `c_time` 。该字段会在更新或插入表数据时自动更新它的时间。如果该表不存在此字段，请将值设置为空字符串或 false。

##### 8.2.3 $updateTime

指定当前表中更新日期的字段名。我们在 `AbstractBase` 类中设置成了 u`_time` 。该字段会在更新或插入表数据时自动更新它的时间。如果该表不存在此字段，请将值设置为空字符串或 false。

##### 8.2.4 $dbOption

这个值在之前介绍过。指定当前 `Model` 使用哪个数据库连接。不是必须的。默认使用 `default` 数据库配置。

#### 8.3 CURD

##### 8.3.1 插入数据

插入数据非常简单。只需要创建一个数组，键名与数据库字段保持一致即可。以向用户表插入数据为例：

```php
$UserModel = new \Models\User();
$data = [
    'mobile'        => '18888888888',
    'open_id'       => 'cbd41c6103064d3f0af848208c20ece2',
    'nickname'      => '无名氏',
    'intro'         => '这家伙太懒，什么都没写',
    'platform'      => 1,
    'last_login_ip' => '127.0.0.1',
];
$insertId = $UserModel->insert($data);
if ($insertId > 0) {
    // 插入成功
} else {
    // 插入失败
}
```



##### 8.3.2 更新数据

更新数据也非常简单。我们以更新用户昵称为例：

```php
$UserModel = new \Models\User();
$data   = [
    'nickname' => 'fingerQin',
];
$where  = ['userid' => 1];
$status = $UserModel->update($data, $where);
if ($status) {
    // 更新成功
} else {
    // 更新失败。
}
```

> 注意：更新失败指的是数据库在执行 SQL 的时候，返回的受影响行数为 0 为依据判断。所以，有可能两次更新的数据一致导致失败。但是，由于我们每次更新都会自动更新创建时间和更新时间。所以，这种情况基本上不会存在。有一种情况一定要引起重视：当我们的关闭了更新时间自动更新的机制或表中并无更新字段，以及我们的程序属于高并发场景。那么，在同一秒中可能会出现更新时间是相同的情况。导致更新失败。

> 所以，写程序一定要以返回的值为 true 来进行程序是否正确为依据。除非明确知道这种情况可以忽略。



##### 8.3.3 删除数据

删除数据，依然还是那么简单。以删除一条用户记录为例：

```php
$UserModel = new \Models\User();
$where  = ['userid' => 1];
$status = $UserModel->delete($where);
if ($status) {
    // 删除成功
} else {
    // 删除失败。
}
```

> 删除和更新一样，都是以数据库记录受影响条数来判断成功与否。



##### 8.3.4 查询数据

1）查询单条记录。

```php
// [1] 指定一个条件。
$UserModel = new \Models\User();
$where  = ['userid' => 1];
$result = $UserModel->fetchOne([], $where);

// [2] 指定返回指定的列。
$columns = ['userid', 'nickname', 'headimg'];
$result = $UserModel->fetchOne($columns, $where);

// [3] 指定排序规则。
$columns = ['userid', 'nickname', 'headimg'];
$orderBy = 'userid DESC,c_time ASC';
$result = $UserModel->fetchOne($columns, $where, $orderBy);

// [4] 指定分组。
$columns = ['userid', 'nickname', 'headimg'];
$orderBy = 'userid DESC,c_time ASC';
$groupBy = 'c_time,userid';
$result = $UserModel->fetchOne($columns, $where, $orderBy, $groupBy);

// [5] 指定查询主库
$result = $UserModel->fetchOne($columns, $where, $orderBy, $groupBy, $isMaster = true);
```

有数据返回一个数组，无数据返回一个空数组。



2）查询多多记录。

```php
// [1] 指定一个条件。
$UserModel = new \Models\User();
$where  = ['userid' => 1];
$result = $UserModel->fetchAll([], $where);

// [2] 指定返回指定的列。
$columns = ['userid', 'nickname', 'headimg'];
$result = $UserModel->fetchAll($columns, $where);

// [3] 指定排序规则。
$columns = ['userid', 'nickname', 'headimg'];
$orderBy = 'userid DESC,c_time ASC';
$result = $UserModel->fetchAll($columns, $where, 0, $orderBy);

// [4] 指定分组。
$columns = ['userid', 'nickname', 'headimg'];
$orderBy = 'userid DESC,c_time ASC';
$groupBy = 'c_time,userid';
$result = $UserModel->fetchAll($columns, $where, 0, $orderBy, $groupBy);

// [5] 指定查询主库
$result = $UserModel->fetchAll($columns, $where, 0, $orderBy, $groupBy, $isMaster = true);

// [6] 指定返回限定条数
$result = $UserModel->fetchAll($columns, $where, 5, $orderBy, $groupBy, $isMaster = true);
```



3）统计记录条数

```php
// [1] 查询。
$UserModel = new \Models\User();
$where = ['c_time' => ['>', '2018-08-08 00:00:00']];
$count = $UserModel->count($where); // 无记录返回 0，有记录时返回实际的条数。
// [2] 指定查询主库
$count = $UserModel->count($where, $isMaster = true);
```



#### 8.4 原生 SQL 操作

有时候我们可能并不想使用封装的方法来写代码。此时我们可以采用原生的 SQL 操作数据。在 `\library\finger\Database\Connection.php` 类中定义了一套跟原生操作相关的方法。

##### 8.4.1 查询数据

```php
// [1] 查询单条数据
$sql    = "SELECT * FROM finger_user WHERE user_id = :user_id";
$params = [':user_id' => 1];
$result = Db::one($sql, $params); // 无数据返回空数组

// [2] 查询多条数据。
$sql    = "SELECT * FROM finger_user WHERE user_id = :user_id";
$params = [':user_id' => 1];
$result = Db::query($sql, $params); // 无数据返回空数组
$result = Db::all($sql, $params); // query 的别名方法。

// [3] 指定主库查询
$result = Db::all($sql, $params, $isMaster = true);
```

可以看到，我们实际是对 PHP PDO 进行了封装，并且使用了 PDO 的 SQL 预处理来防 SQL 注入。所以，大家在使用的时候，一定用这种方式来进行 SQL 防注入。

##### 8.4.2 插入/更新/删除

```php
$sql = "INSERT INTO finger_user (mobile,open_id) VALUES(:mobile, :open_id)";
$params = [
    ':mobile'  => '18888888888',
    ':open_id' => 'xxxx'
];
$insertId = Db::execute($sql, $params);
if ($insertid) {
    // 操作成功
} else {
    // 操作失败
}
```

与查询一样，我们依然使用了 PDO 提供的语法，只是做了简单的封装。大家在使用的时候，一定要用 PDO 提供的预处理参数的语法。这才能杜绝 SQL 注入的情况。



#### 8.5 事务

事务操作非常简单。

```php
// [1] 方式 1
Db::beginTransaction(); // 开启事务。
try {
    // ......
    Db::commit(); // 提交事务。
} catch (\Exception $e) {
    Db::rollBack(); // 回滚事务。
}

// [2] 方式 2
$UserModel = new \Models\User();
$UserModel->beginTransaction(); // 开启事务。
try {
    // ......
    $UserModel->commit(); // 提交事务。
} catch (\Exception $e) {
    $UserModel->rollBack(); // 回滚事务。
}
```

> 因为事务是属于库级别而不是表级别的。那么，在 Model 级别的事务操作依然是对数据库级别的操作。

#### 8.6 Where 语法

由于 `where` 语法比较丰富。所以，在上面的小节并未涉及太多。本小节专门来讲解 `where` 语法的使用。

在 `finger\Database\Models` 类当中的 `parseWhereCondition` 方法是用来专门解析 `where` 条件用的。

```php
// [1] 等于
$where = [
    'userid'     => 1,
    'cur_status' => 1
];

// [2] 大于
$where = [
    'userid' => ['>', 1]
];

// [3] 大于等于
$where = [
    'userid' => ['>=', 1]
];

// [4] 小于
$where = [
    'userid' => ['<', 1]
];

// [5] 小于等于
$where = [
    'userid' => ['<=', 1]
];

// [6] 不等于
$where = [
    'userid' => ['!=', 1]
];
// 另一种写法。不推荐。
$where = [
    'userid' => ['<>', 1]
];

// [7] LIKE
$where = [
    'mobile' => ['LIKE', "%138"]
];

// [8] IN
$where = [
    'mobile' => ['IN', [1, 2, 3]]
];

// [9] NOT IN
$where = [
    'mobile' => ['NOT IN', [1, 2, 3]]
];

// [10] BETWEEN
$where = [
    'mobile' => ['BETWEEN', [1, 2]]
];
```



目前我们只支持以上的表达式。但是，我们有时候会写 `OR` 这种写法。对于这种 `where `，我只能说对不起咱们不支持。那要怎么做呢？少年，用原生语法吧。



#### 8.7 联合查询 JOIN

联合查询是我们在一个应用当中必不可少的部分。但是，咱们封装的 `DbBase` 不支持。所以，请使用原生的 `SQL` 吧。其实，我们也可以封装。只是不喜欢这种重量级的操作。原生的 `SQL` 更容易书写，不存在框架差异。任何框架我们都能用 SQL 直接操作。



#### 8.8 SQL 执行记录

我们的应用在执行过程中会将所有执行的 `SQL ` 记录下来。当然，除了正式环境会关闭 `SQL` 日志的记录外，在开发、预发布、公测都会正常记录。便于我们排查问题。

日志记录位置：`根目录\logs\sql`



#### 8.9 注意事项

##### 8.9.1 ping 命令使用

一般在 Web 开发中，我们与 MySQL 服务器建立一个连接，然后在脚本执行结束会自动关闭。通常这个时间都会在几秒钟内。MySQL 与我们的 PHP 创建的链接并不会连接超时。因为，MySQL 服务器通过允许的最大连接空闲时间都在分十分钟或几十小时。而我们 PHP 允许的最大由 PHP 配置 `default_socket_timeout` 控制，默认也在 60 秒。

当我们要编写常驻后台运行的 PHP 脚本的时候，那么此时 60s 的时间肯定是不够的。最好是允许我们无限时间。所以，为了保证连接的 MySQL 不会出现超时，我们每次执行的时候进行了 ping()。ping() 服务器与当前 PHP 脚本的通道是否已经被服务器掐断了。

**结论：在编写常驻进程业务的时候，记得每次执行操作之前使用 `finger\DbBase` 提供的 ping() 方法进行检测服务器链接。避免出现 timeout 错误而导致程序报错退出。同时要设置 PHP 配置文件 `default_socket_timeout` 为 -1 值。**

```
// ping 命令使用
$dbConn = new \finger\Database\Connection('default'); // default 指定使用哪个数据库配置。
$dbConn->ping(); // 检测当前连接是否有效，无效则直接重连。
```



##### 8.9.2 主从读写

由于我们使用了阿里云的 RDS 数据库产品。本身它是自带主从集群的特性。 MySQL 主从是通过 binlog 日志进行数据同步。同步就一定会存在延迟。在一些特定环境，对实时环境要求极高的环境，这会导致刚插入的数据去查询的时候会导致数据取不到的情况。

**结论：实时性要求极高的环境，请显示将查询指向主库读取。**



##### 8.9.3  Model 目录规划

我们在开发项目过程中通常是单库操作。如果在多库操作时。那么目录规划就显得有必要了。不然，两个库有同样的表名，在命名的时候就会产生冲突。让自己非常的纠结。建议在 Models 目录下每个库一个目录进行区分。



### 9 Redis

在中小型应用当中，Redis 以其数据类型丰富、性能强悍以及简单易用占有重要一席。所以，在我们的基建项目中，大量采用了 Redis 做为缓存的首选服务。如：Session、Cache、日志队列、短信队列。轻松应对千万级 PV 不成任何问题。

#### 9.1 Redis 配置

在我们的 `config.ini` 配置文件当中，我们有如下配置段：

```
; Redis 配置。
redis.default.host  = 127.0.0.1
redis.default.port  = 6379
redis.default.auth  = f6bea7c6f222831c658d89e49930d936
redis.default.index = 1
```

系统当中所有的 Redis 操作都是在此配置连接上进行。所以，暂时并不支持不同的业务使用不同的 Redis 服务器。

#### 9.2 缓存

我们在 `\Utils\YCache` 类中封装了一系列快捷操作缓存的方法。

##### 9.2.1 缓存读取

```php
$result = YCache::get("cache_key");
```

##### 9.2.2 缓存设置

```php
// [1] 
$userid = 888888;
$status = YCache::set("cache_key", $userid); // 成功返回 true,失败返回 false

// [2] 支持直接保存数组。取出来的时候，也会还原来数组。
$data = [
    'userid'   => 888888,
    'nickname' => 'fingerQin'
];
$status = YCache::set("cache_key", $data);

// [3] 设置缓存时间。单位（秒）。
$data = [
    'userid'   => 888888,
    'nickname' => 'fingerQin'
];
$status = YCache::set("cache_key", $data, 60);
```

##### 9.2.3 缓存删除

```php
$status = YCache::delete("cache_key");
```

##### 9.2.4 自增/自减

```php
// [1] 自增，默认自增1
$val = YCache::incr("cache_key"); // 返回自增之后的值。

// [2] 自增指定值
$val = YCache::incr("cache_key", 5);

// [3] 自减，默认自减1
$val = YCache::decr("cache_key");

// [4] 自减指定值
$val = YCache::decr("cache_key", 5);
```

##### 9.2.4 缓取 Redis 对象

当默认提供的缓存操作不能满足我们的业务的时候。可以直接获取 Redis 对象。就可以使用 Redis 提供的更多更棒的特性。关于 Redis 扩展相关的文档在 GitHub：https://github.com/phpredis/phpredis 。

我们提供如下方法快速获取 Redis 对象。

```php
$redis = YCache::getRedisClient();
```



### 10 验证器

在日常开发中，对数据的验证是必不可少的部分。验证器类定义在 `finger\Validator` 中。

#### 10.1 验证器种类

- 手机号验证器(mobilephone)。
- 座机验证器(telephone)。
- IP地址验证器(ip)。
- 邮编验证器(zipcode)。
- 身份证验证器(idcard)。
- 邮箱验证器(email)。
- URL 验证器(url)。
- MAC 物理地址验证器(mac)。
- 银行卡号验证器(bankcard)。
- QQ 号验证器(qq)。
- 中文验证器(chinese)。
- 字母验证器(alpha)。
- 数字验证器(number)。
- 整型验证器(integer)。
- 浮点型验证器(float)。
- 布尔值验证器(boolean)。
- UTF8字符验证器(utf8)。
- 日期验证器(date)。
- 字母数字下线线破折号验证器(alpha_dash)。
- 字母范围验证器(alpha_between)。
- 数字范围验证器(number_between)。
- 字符串长度验证器(len)。
- 字符串必传验证器(require)。
- 字母或数字验证器(alpha_number)。
- 日期时间比较验证码(date_compare)。

#### 10.2 验证器使用

```php
$data = [
    'username' => 'fingerQin',
    'password' => '123456',
    'email'    => '',
    'birthday' => '1988-08-08'
];

$rules = [
    'username' => '用户名|require|len:6:20:0|alpha',
    'password' => '密码|require|alpha_dash|len:6:20:0',
    'email'    => '邮箱|email',
    'birthday' => '生日|date:0'
];

\finger\Validator::valido($data, $rules);
```

`valido` 方法验证不通过会直接抛出异常。通过我们向表插入数据或业务方法接收参数的时候可以用此来进行参数验证。避免穿透到真正执行业务时报错。避免不必要的性能消耗以及破坏用户的体验。

#### 10.3 单独使用验证器

有时候，我们可能仅仅只想验证单个值。不希望用那么复杂验证器。则可心单独拆开使用。如下所示：

```php
// [1] 验证 URL
$url = "https://www.phpjieshuo.com"
if (!\finger\Validator::is_url($url)) {
    // URL 格式不正确
}

// [2] 验证数值范围
$number = 20;
if (!\finger\Validator::is_number_between($number, $start = 0, $end = 100)) {
    // 值不在 0 ~ 100 之间。
}
```



### 11 文件上传

文件上传可以说是任何一个互联网项目基本上必须的功能。我们的基建项目默认使用阿里云的 Oss 产品来存储我们的文件。

#### 11.1 配置文件

```
; 上传驱动配置
upload.driver   = oss
upload.save_dir = 

; 阿里云 OSS 配置。
oss.access_key    = ******************
oss.access_secret = ******************
oss.endpoint      = ******************
oss.bucket        = ******************
```

关于阿里云 Oss 不熟悉的地方，可以查看官方文档。

#### 11.2 上传图片

```php
$upload = new \finger\Upload();
$re     = $upload->uploadOne($_FILES['image']);
$path   = '';
if ($re) {
	$path = $re['savepath'].$re['savename'];
}
```

当然，这还是非常的简陋。如果，想对文件大小，类型进行限制的话。`\finger\Upload` 类还提供了相关的方法或参数来加强这些判断。



### 12 API 接口

因为 Yaf-Server 基建项目，主要的功能就是对 APP、活动、其他场景提供接口服务。所以，API 接口在基建项目的设计可以说是非常重要的一环。各位少年勿必认真阅读本小节。



#### 12.1 接口入口

Yaf-Server 基建项目的入口与我们的 API 入口不同。我们的 API 入口指的是项目当中 `Index模块/Index控制器/Index`动作。

因为，我们在配置文件当中设置了默认的模块/控制器/动作都是 `Index`。所以，访问接口的时候，就不需要增加这些访问路径了。如：`https://api.phpjieshuo.com` 。

打开入口文件，我们可以看到方法中定义了一个 `IS_API` 常量。这是为了方便在其他位置能知道当前访问是来源于 API 接口。

其次，我们我们接着设置了以下两行代码：

```php
header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');
```

允许跨域访问我们的接口。这样只要拥有我们的接口密钥就可以在任何环境请求到我们的接口。同时我们返回的数据类型为 JSON。浏览器或客户端可以通过这个头能识别。在一些友好的工具里面，可以自动格式返回的数据显示。

接着我们获取接口的两个主要参数：`data` 与 `sign`。

- `data`：保存了 `JSON`。包含了接口当中要求的参数。
- `sign`：接口参数签名。服务端会验证签名是否合法。

> 关于接口路由下一小节再讲解。

为了便于了解用户的行为，以及后续运营过程的问题排查。我们记录了用户的如下信息：

- `userid`：如果当前用户已经登录，则记录用户的 ID。否则为 0。
- `ip`：`ip` 地址。可以知道我们用户分布的情况。
- `datetime`：请求时间。当入库的时候，我们可以查询指定时段的用户行为。也可以为以后的行为分析做支撑。
- 接口数据。这是必须的日志。

我们把接口请求日志记录到了 `logs/apis` 目录下，并按天进行日志分割。



#### 12.2 接口路由

当我们的入口接收到数据之后，我们的接口工厂类 `\Apis\Factory` 会根据当前请求参数当中的 `method` 参数值进行接口对象的生成。

步骤如下：

- 验证 `method` 与 `v` 参数是否合法。这两个参数是路由关键。
- `method` 转类名。如：`user.login` 转成 `UserLoginApi` 。
- `v` 转版本目录。如：`1.0.0` 转 `v100` 。
- 得到接口类名。如 `user.login` 最终会得到 `\Apis\app\v100\User\UserLoginApi`。
- 再通过 `class_exists` 方法验证接口对应的接口类是否存在。不存在则报：您的APP太旧请升级。



#### 12.3 接口编写

##### 12.3.1 接口类别

我们的接口提供了三种类别。

- `APP` 调用。
- 活动调用。
- 管理后台调用。

它们之间不能互相调用。即分配给 `APP` 的密钥不能调用活动的接口。从而达到越权的隔离。那么，我们系统是怎样区分的呢？

- 以 `activity` 开头的接口，全部为活动接口。如：`activity.system.send` 。
- 以 `admin` 开头的接口，全部为管理后台专用接口。如：`admin.user.info` 。
- 其他打头的接口全部为 APP 调用的接口。所以，不要用 `activity` 和 `admin` 定义 APP 调用的接口。

其次，我们分配给客户端/活动方/管理后台的密钥都会在 `finger_api_auth` 表中进行登记。当每次接口访问的时候，我们会进行两相对比。类别不对，则判定接口权限不足。



##### 12.3.2 创建接口

以创建 `user.login` 接口版本号为 `1.0.0` 接口为例。

我们此时要在 `\library\Apis\app\v100\User` 目录创建一个名为 `UserLoginApi.php` 的接口文件。接口文件当中必须定义一个 `UserLoginApi` 的类。并且该类必须继承 `\Apis\AbstractApi` 类。该类当中定义了一系列与接口请求响应签名验证相关的方法。大多数数方法都是自动调用不需要理会。唯独必须实现其中的抽象方法 `runService` 方法。该方法主要的作用是完成参数的接收与业务方法的调用。

目录结构如下：

```
├── library
│   ├── Apis
│   │   ├── AbstractApi.php
│   │   ├── Factory.php
│   │   ├── app
│   │   │   └── v100
│   │   │       ├── Sms
│   │   │       │   └── SmsSendApi.php
│   │   │       ├── System
│   │   │       │   ├── SystemInitApi.php
│   │   │       │   ├── SystemUpgradeApi.php
│   │   │       │   └── SystemUploadApi.php
│   │   │       └── User
│   │   │           ├── UserLoginApi.php
│   │   │           └── UserRegisterApi.php
```

`UserLoginApi` :

```php
namespace Apis\app\v100\User;

use Apis\AbstractApi;
use Services\User\Auth;

class UserLoginApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @see Api::runService()
     * @return void
     */
    protected function runService()
    {
        $mobile      = $this->getString('mobile');
        $password    = $this->getString('password');
        $type        = $this->getString('type');
        $deviceToken = $this->getString('device_token', '');
        $platform    = $this->getString('platform', 0);
        $appV        = $this->getString('app_v', '');
        $result      = Auth::login($mobile, $password, $type, $deviceToken, $platform, $appV);
        $this->render(STATUS_SUCCESS, '登录成功', $result);
    }
}
```

通过以上示例代码可以看到，我们继承了 `AbstractApi` 类，并且实现了父类当中的 `runService` 抽象方法。在 `runService` 方法中，我们通过调用一系列快捷方法 (`$this->getString()`)  获取接口参数。再调用业务方法完成本次接口的调用。

最后，我们调用 `render` 方法完成了整个接口的调用。



##### 12.3.3 快捷方法

当客户端请求我们的 API 接口时，会根据接口文档传递我们接口所要求的参数。那么，在获取接口参数的时候，我们为了方便快捷准确获取到我们想要的值。 我们在 `AbstractApi` 当中定义了一系列快捷获取参数的方法。

**1）getInt()**

获取指定参数并验证参数是否为整型。如果不是整型则报错。如果是数字字符串不会报错，并转换为整型返回。

**2）getString()**

获取指定参数并验证参数是否为字符串。如果不是字符串则报错。如果是数字字符串不会报错，并转换为字符串返回。Null 或 数组肯定会报错。

**3）getFloat()**

获取指定的参数并验证参数是否为 Float 类型。如果不是 Float 类型则报错。如果是数字或数字类型的字符串则会被转换为 Float 类型返回。

**4）getArray()**

获取指定参数并验证参数是否为数组类型。如果不是则报错。

> 注：以上四个快捷方法，都有第二个参数。当找不到指定值的时候，此参数被设置为不是 Null 值的时候，会返回该设置的值。



#### 12.4 IP 白名单

我们的接口不仅供 APP 调用，也会供活动调用以及管理后台调用。后续可以会开放给外部的合作方使用。供 APP 调用的时候，我们不会对调用端的 IP 进行限制。只要密钥签名验证通过即可访问。

但是，活动调用和管理后台调用的时候，我们可能就要对 IP 进行限制了。毕竟，给活动或管理后台提供的接口权限相比是很大的。

在添加 App 应用的时候，可以指定哪些 IP 或 IP 段能访问我们的此 App 应用接口。





#### 12.5 可写接口访问控制

我们有时候版本上线内测之间，不允许数据库存在外部用户数据写入，此时我们可以关闭接口的对外访问。在我们的 `AbstractApi` 类定有一个方法 `isAllowAccessApi` 。该方法需要读取 `config.ini` 配置中的如下配置：

```ini
; 可写接口是否可访问
api.write_access  = 1
; 可写接口不可访问时的提示信息
api.write_close_msg = 产品升级维护中
; 可写接口不可访问时哪些用户可以访问(内部测试用)
api.write_userids =
```

> 该配置文件定义了当前可写的接口是否允许访问，当不允许访问时，我们提供什么信息给用户，以及内部哪些用户可以访问。

 该方法不会被自动调用。主要有两个原因没有自动调用：

1）暂时没有可靠的方法定义哪些方法是可写方法。

2）有些接口跟版本号关联，以及需要登录状态等判断。

使用如下：

```php
class SmsSendApi extends AbstractApi
{
    /**
     * 逻辑处理。
     * 
     * @return void
     */
    protected function runService()
    {
        $this->isAllowAccessApi(0);
        $mobile      = $this->getString('mobile', '');
        $key         = $this->getString('key', '');
        $platform    = $this->getString('platform');
        $channel     = $this->getString('channel', '');
        $deviceToken = $this->getString('device_token', '');
        $appV        = $this->getString('app_v', '');
        $result      = Sms::send($mobile, $key, '', $platform);
        $this->render(STATUS_SUCCESS, '发送成功', $result);
    }
}
```

> 注：如果当前是需要用户登录才能访问的，那么需要将参数设置为当前用户的用户 ID。



#### 12.6 接口命名规范

一个好的接口名字让人很容易理解。所以，我们给接口的命名定义了几个必须考虑的准则：

- APP 调用的接口第一个前缀最好是模块相关的词。

- 每个接口最后一个词必须是动词。如：user.edit、user.delete。

- 如果功能复杂，接口中间的词是子模块关联词。如：user.address.edit。

  

#### 12.7 多版本支持

 我们的系统需求会随时时间的推移而不断变化。那么，接口也会相应发生变化。低版本的接口肯定无法满足高版本接口。换句话说，低版本的接口无法满足新需求。此时，我们需要对低版本接口进行升级满足新需求。亦或，我们需要增加新的接口来满足新的需求。如此种种。具体步骤如下：

**1）旧版本代码复制一份到新版本**

把 `library\Apis\app\v100` 目录复制为 `v101`。意思是说，我们的 v101 是 API 1.0.1 版本。然后，把每个接口文件里面的 `namespace` 命令空间包含的 `v100` 悉数替换为 `v101`  即可。

**2）更改对应的接口**

如果旧接口不满足需求，可能会出现三种情况。

- 需要增加新参数才能做精细的处理筛选或做记录。此时，返回的数据没有任何变化。或者变化的时候，客户端/触屏端/PC端/其他端无须操作即可自动更改。
- 



#### 12.8 注意事项

- 每个可写接口必须调用 `isAllowAccessApi()` 方法。并根据是否需要登录设置其调用位置。
- 非 APP 调用时，都必须将调用方的服务器 IP 全部设置到配置文件当中的内部服务器 IP 配置。



### 13 命令行

命令行运行，是每个项目必不可少的功能。它通常运用在一些定时脚本或常驻进程业务。

#### 13.1 入口文件

由于命令行运行与 Web 模式运行的机制不一样。所以，我们的命令行模式也有独立的入口文件。它在 `public/cli.php` 。

主要做了以下事情：

- 定义必须的常量。
- 加载自定义常量脚本。
- 加载配置文件。
- 解析路由参数。
- 根据路由分发请求。

> 注：为了安全，我们做了两件事情。一：限制所有命令行运行的程序只能映射到 Cli 模块。因为，命令行对应的模块都是处理一些业务复杂、权限较高、性能损耗较大的业务。二：限制 Cli 模块的所有请求，必须是通过 Cli 模块到达。可以在公共的 `Common\controllers\Cli` 类中看到相应的判断。



#### 13.2 创建命令行模式控制器

创建命令行模式的控制器非常简单。与 Web 模式创建方式大致一样。主要的两个区别如下：

- 控制器继承的控制器变成了 `\Common\controllers\Cli` 。
- 控制器必须定义在 `apps\modules\controllers` 目录。不能与 Web 模式的混在一起。



示例代码如下：

```php
use finger\Utils\YLog;

class LogController extends \common\controllers\Cli
{
    public function writeMongoDbAction()
    {
        YLog::writeMongo();
    }
}
```

这是一个将队列当中日志写入 MongoDb 的常驻进程。



#### 13.3 运行程序

 进入 `cli.php` 所在文件夹，执行如下命令：

```
$ php cli.php Log/writeMongoDb
```

注意：在一些安装了多个版本的 PHP 的服务器上，PHP 的命令的版本一定要与 Yaf 框架的版本对应。Log 代表执行 Cli 模块下的 LogController，`writeMongoDb` 代表 LogController 里面的 `writeMongoDbAction` 。



### 14 内嵌页

在 APP 开发的时候，我们经常会有一些用户协议等需要提供页面。实际上我们可以不用提供。直接提供接口，客户端渲染即可。碍于客户端有时候不想实现这些界面。我们做出了相应的改变。



所有的内嵌页，全部放入 `Embed` 模块。与其他模块一样，只是继承了单独的公共控制器 `\Common\controllers\Guest` 。

> 暂时未支持传递用户信息显示。如果确实需要可以通过由前端单独调用接口实现带用户信息的数据展示。



### 15 环境配置适配

在日常开发中，我们经常会根据不同的环境（本地、开发、预 发、公测、正式）之间来回切换调试或部署服务器。此时针对不同环境的配置适配就显得尤为重要。在本项目当中，我们通过在根目录下创建 `.env` 文件来进行环境的适配。比如，我们此时在本地开发调试代码。此时，我们的数据库、Redis 等配置肯定与线上是不一样的。但是，又不可直接对配置文件 `/config/config.ini` 进行修改。我们可以把想修改的部分配置在 `.env` 文件当中进行差异化配置。正式环境时，删除 `.env`  文件即可。

差异化配置文件的主要实现均由 `YCore::appconfig()` 方法实现。

以下是 `.env` 配置文件示例：

```ini
; 当前环境:dev、pre、beta、pro。已经在 constants.php 当中定义了常量对应。
app.env = ENV_DEV

; 调试模式。与调试信息相关。
app.debug = FALSE

; 域名配置
domain.api     = http://local.api.hunshijians.com/
domain.files   = http://files.hunshijian.com/
domain.statics = http://local.statics.hunshijian.com/
domain.embed   = http://local.embed.hunshijian.com/
domain.www     = http://local.www.hunshijian.com

; Redis 配置。
redis.default.host  = 127.0.0.1
redis.default.port  = 6379
redis.default.auth  = 
redis.default.index = 4
```



通常，我们不需要提交该文件。正式环境部署的时候直接删除该文件即可。





