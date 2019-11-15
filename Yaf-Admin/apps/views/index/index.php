<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1, user-scalable=no">
    <title>后台管理系统</title>
    <link href="{{'bootstrap.min.css'|css}}" title="" rel="stylesheet" />
    <link title="" href="{{'body.css'|css}}" rel="stylesheet" type="text/css"  />
    <link title="" href="{{'style.css'|css}}" rel="stylesheet" type="text/css"  />
    <link title="blue" href="{{'dermadefault.css'|css}}" rel="stylesheet" type="text/css"/>
    <link href="{{'templatecss.css'|css}}" rel="stylesheet" title="" type="text/css" />
    <script src="{{'jquery-1.11.1.min.js'|js}}" type="text/javascript"></script>
    <script src="{{'bootstrap.min.js'|js}}" type="text/javascript"></script>
    <!--[if lt IE 9]>
    　 <link href="{{'ie8.css'|css}}" rel="stylesheet" title="" type="text/css" />
    <![endif]-->
    <style type="text/css">
    .nav-active{
        background-color: #0087b4
    }
    .side-active{

    }
    .myLeft{
        float:left !important;
        width:91.5%;
    }
    .item_htli{
        width:118px;
    }
    .nav>li.item_htli>a{
        text-align: center;
        padding-left: 0;
        padding-right: 0;
    }
    .myLeft {
        float: left !important;
        width: 64.5%;
    }
    .nav-top-r{
        float: right;
        line-height: 20px;
    }
    .scrollbar{
        width: 166px;
        height: 100%;
        overflow: auto;
        float: left;
        border: none;
    }
    .scrollbar-1::-webkit-scrollbar {/*滚动条整体样式*/
        width: 5px;     /*高宽分别对应横竖滚动条的尺寸*/
        height: 1px;
    }
    .scrollbar-1::-webkit-scrollbar-thumb {/*滚动条里面小方块*/
        border-radius: 5px;
        -webkit-box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
        background: #535353;
    }
    .scrollbar-1::-webkit-scrollbar-track {/*滚动条里面轨道*/
        -webkit-box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
        border-radius: 5px;
        background: #EDEDED;
    }
    </style>
</head>
<body>
<nav class="nav navbar-default navbar-mystyle navbar-fixed-top">
    <div class="navbar-header" >
        <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand mystyle-brand" href="{{'Index/index'|url}}"><span class="glyphicon glyphicon-home"></span></a> </div>
    <div class="collapse navbar-collapse ">
        <ul class="nav navbar-nav ">
            <li class="li-border item_htli"><a class="mystyle-color" href="{{'Index/index'|url}}">后台管理系统</a></li>
        </ul>

        <ul class="nav navbar-nav pull-right myLeft ">
            {{foreach $top_menu as $menu}}
                <li class="li-border nav-right {{if $menu.menuid == 1}} nav-active {{/if}}"><a href="javascript:void(0)" class="topClick " rel="{{'Index/left'|url}}?menu_id={{$menu.menuid}}&{{$menu.ext_param}}" class="mystyle-color">{{$menu.menu_name}}</a></li>
            {{/foreach}}
        </ul>
        <ul class="nav navbar-nav pull-right nav-top-r">
            <li class="dropdown li-border"><a href="#" class="dropdown-toggle mystyle-color" data-toggle="dropdown">{{$mobilephone}}<span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="{{'Admin/editPwd'|url}}" target="right">修改密码</a></li>
                    <li class="divider"></li>
                    <li><a href="javascript:void(0)" id="logout">退出登录</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
<div class="down-main">
    <div class="left-main left-full">
        <div id="subNavBox" class="subNavBox scrollbar scrollbar-1">
            {{include file='index/left.php'}}
        </div>
    </div>
    <div class="right-product view-product right-full">
        <iframe name="right" id="external-frame" src="{{'Index/right'|url}}" marginwidth=0 width="100%" height="98%" frameBorder=0 scrolling=yes>
        </iframe>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        // 导航事件 
        $(".topClick").click(function(){
            var _route = $(this).attr("rel");
            $.ajax({
                type: 'get',
                url: _route,
                dataType:'json',
                success: function(rsp) {
                    if (rsp.code == 200) {
                        $('#subNavBox').html(rsp.data.html);
                    } else {
                        if (rsp.code == 501 || rsp.code == 502 || rsp.code == 503) {
                            popup(3, 6, rsp.msg, 1000, "{{'Public/login'|url}}");
                        }
                        popup(3, 6, rsp.msg, 1000);
                    }
                }
            });
        });
        $(".nav-right").click(function() {
            $('.nav-right').removeClass('nav-active')
            $(this).addClass('nav-active')
        });
        /*左侧导航栏显示隐藏功能*/
        $("#subNavBox").on('click', '.subNav',function() {
            /*显示*/
            if($(this).find("span:last-child").attr('class')=="title-icon glyphicon glyphicon-chevron-down") {
                $(this).find("span:last-child").removeClass("glyphicon-chevron-down");
                $(this).find("span:last-child").addClass("glyphicon-chevron-up");
                $(this).removeClass("sublist-down");
                $(this).addClass("sublist-up");
            } else { /*隐藏*/
                $(this).find("span:last-child").removeClass("glyphicon-chevron-up");
                $(this).find("span:last-child").addClass("glyphicon-chevron-down");
                $(this).removeClass("sublist-up");
                $(this).addClass("sublist-down");
            }
            // 修改数字控制速度， slideUp(500)控制卷起速度
            $(this).next(".navContent").slideToggle(300).siblings(".navContent").slideUp(300);
        })
        /*左侧鼠标移入提示功能*/
        $(".sBox ul li").mouseenter(function() {
            if($(this).find("span:last-child").css("display")=="none")
            {$(this).find("div").show();}
        }).mouseleave(function(){$(this).find("div").hide();})
        $('#logout').click(function () {
            $.ajax({
                type: 'post',
                dataType:'json',
                url: "{{'Public/logout'|url}}",
                success: function(rsp) {
                    if (rsp.code == 200) {
                        window.location.href = "{{'Public/login'|url}}"
                    } else {
                        popup(3, 6, rsp.msg, 1000);
                    }
                }
            });
        });
    })
</script>
</body>
</html>
