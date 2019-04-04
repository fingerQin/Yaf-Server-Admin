## Yaf-Server 部署文档##

### 1 环境说明

```json
Nginx   -> Nginx 1.x (1系列最新版)
PHP     -> PHP 7.1.x 以上(含)
MySQL   -> MySQL 5.6.x
Redis   -> Redis 3.x (3系列最新版即可)
MongoDB -> MongoDB 3.x (3系列最新版本)
```



### 2  PHP 扩展安装

#### 2.1 Yaf 扩展安装

因为我们的 Yaf-Server 基建项目用是基于 Yaf 框架开发，所以必须安装 Yaf 扩展。

```shell
$ wget http://pecl.php.net/get/yaf-3.0.7.tgz
$ tar zxvf yaf-3.0.7.tgz
$ cd yaf-3.0.7
$ phpize
$ make && make install
```

然后，在 php.ini 配置文件末尾增加如下代码： 

```ini
extension = yaf.so
yaf.use_spl_autoload = 1
```

> 注：这里我要说明一下：PHP 5.x 系列安装 yaf-2.x 系列最新版本的扩展，PHP 7.x 系列安装 yaf-3.x 系统最新版本的扩展。



#### 2.2 Redis 扩展安装

Redis 我们用来做系统缓存、Session 分布式存储、消息队列等功用。所以必须安装 Redis 扩展。我们安装最新稳定版本的 Redis 扩展即可。

```shell
$ wget http://pecl.php.net/get/redis-4.1.0.tgz
$ tar zxvf redis-4.1.0.tgz
$ cd redis-4.1.0
$ phpize
$ make && make install
```

然后，在 php.ini 配置文件末尾增加如下代码： 

```ini
extension = redis.so
```



#### 2.3  event 扩展安装[可选]

考虑到后续系统可能会使用诸如一些事件相关的函数或第三方扩展。如 `workerman` 。所以，我们推荐安装此扩展。

```shell
$ wget http://pecl.php.net/get/event-2.3.0.tgz
$ tar zxvf event-2.3.0.tgz
$ cd event-2.3.0
$ phpize
$ make && make install
```

然后，在 php.ini 配置文件末尾增加如下代码：

```ini
extension = event.so
```

> 注：在安装此扩展之前，我们要先安装 `libevent` 扩展。



#### 2.4 其他扩展

在开发中，我们会使用到 PHP 内置的一些方法。而这些方法需要 PHP 内置扩展支持。有些内置扩展默认是关闭的。有些扩展安装的时候被关闭了。所以，我们在安装的时候要确保以下扩展是开启状态。

```
posix
pcntl
openssl
mcrypt
json
ftp
gd
ctype
```



### 3 Nginx 配置

假设我们的相关信息如下：

- 域名：local.server.yaf.com
- 项目入口目录：/data/wwwroot/codespace/myself/Yaf-Server/public

```nginx
server {
  listen 80;
  server_name local.server.yaf.com;
  access_log /data/wwwlogs/local.server.yaf.com_nginx.log combined;
  index index.html index.htm index.php;
  include /usr/local/nginx/conf/rewrite/yaf.conf;
  root /data/wwwroot/codespace/myself/Yaf-Server/public;
  
  #error_page 404 = /404.html;
  #error_page 502 = /502.html;
  
  location ~ \.php {
    fastcgi_pass remote_php_ip:9000;
    fastcgi_index index.php;
    include fastcgi_params;
    set $real_script_name $fastcgi_script_name;
    if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
      set $real_script_name $1;
      #set $path_info $2;
    }
    fastcgi_param SCRIPT_FILENAME $document_root$real_script_name;
    fastcgi_param SCRIPT_NAME $real_script_name;
    #fastcgi_param PATH_INFO $path_info;
  }
  location ~ .*\.(gif|jpg|jpeg|png|bmp|swf|flv|mp4|ico)$ {
    expires 30d;
    access_log off;
  }
  location ~ .*\.(js|css)?$ {
    expires 7d;
    access_log off;
  }
  location ~ /\.ht {
    deny all;
  }
}
```

 `/usr/local/nginx/conf/rewrite/yaf.conf` 包含了如下信息：

```ini
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=$1 last;
        break;
    }
}
```



### 4 hosts 配置

因为我们是在本地开发环境配置。所以，我们需要配置系统 hosts 文件。

```ini
127.0.0.1 local.server.yaf.com
```



### 5 日志目录可写权限###

项目根目录下的 logs 目录是存放项目所有日志的位置。请把它设置为可写。

```
$ chmod -R 0777 ./logs
```



###6 Composer update

 

由于 Yaf-Server 使用 Composer 安装了 Mongodb 等第三方包。所以，我们首次部署环境时，需要把这些包下载回来。

 

**请确认自己环境已经安装了 Composer**

 

进入项目根目录执行如下命令进行安装第三方包:

 ```
composer update
 ```


### 7 账号密码 ###
```
账号：13812345678
密码：123456
```
管理后台登录的时候会请求 Yaf-Server 项目的发短信的接口。配置文件当中环境设置为开发时，验证码默认为：123456。但是，请务必保证管理后台能调用到 Yaf-Server 的短信接口。






