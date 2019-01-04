<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1, user-scalable=no">
    <title>后台管理系统</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="{{'bootstrap.min.css'|css}}" title="" rel="stylesheet" />
    <link title="" href="{{'body.css'|css}}" rel="stylesheet" type="text/css"  />
    <link title="" href="{{'style.css'|css}}" rel="stylesheet" type="text/css"  />
    <link title="blue" href="{{'dermadefault.css'|css}}" rel="stylesheet" type="text/css"/>
    <link href="{{'templatecss.css'|css}}" rel="stylesheet" title="" type="text/css" />
    <script src="{{'jquery-1.11.1.min.js'|js}}" type="text/javascript"></script>
    <script charset="utf-8" src="{{'sea2.3.0.js'|js}}"></script>
    <script charset="utf-8" src="{{'combo.js'|js}}"></script>
    <script src="{{'bootstrap.min.js'|js}}" type="text/javascript"></script>
    <script src="{{'layui/layui.all.js'|js}}" type="text/javascript"></script>
    <script src="{{'common.js'|js}}" type="text/javascript"></script>

    <!-- SimpleAjaxUpload 插件 start -->
    <script src="{{'/AjaxUploader/SimpleAjaxUploader.min.js'|js}}"></script>
    <!-- SimpleAjaxUpload 插件 end -->

    <!-- 时间插件 start -->
    <link rel="stylesheet" type="text/css" href="{{'/calendar/jscal2.css'|js}}" />
    <link rel="stylesheet" type="text/css" href="{{'/calendar/border-radius.css'|js}}" />
    <link rel="stylesheet" type="text/css" href="{{'/calendar/win2k.css'|js}}" />
    <script type="text/javascript" src="{{'/calendar/calendar.js'|js}}"></script>
    <script type="text/javascript" src="{{'/calendar/lang/en.js'|js}}"></script>
    <!-- 时间插件 end -->
  
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://cdn.bootcss.com/html5shiv/r29/html5.min.js"></script>
      <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<style type="text/css">
  html {
    _overflow-y: scroll
  }
  .pull-right{ line-height:38px; }

  /* 帮助文档样式 */
  #helpstr {
    padding:10px;
  }
  #helpstr-editor {
      display:none;
  }
  #helpstr-button {
      padding: 5px;
      margin: 10px;
      z-index: 9999;
  }
</style>