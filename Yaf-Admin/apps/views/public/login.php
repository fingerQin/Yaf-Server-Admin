<!DOCTYPE html>
<!-- saved from url=(0050)http://local.jinradmin.jinr.com/Index/Public/Login -->
<html xmlns="http://www.w3.org/1999/xhtml"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>《腿神》管理后台登录</title>
    <link href="{{'bootstrap.min.css'|css}}" title="" rel="stylesheet">
    <link href="{{'body.css'|css}}" rel="stylesheet" type="text/css">
    <link href="{{'login.css'|css}}" rel="stylesheet" type="text/css">
    <script src="{{'jquery-1.11.1.min.js'|js}}" type="text/javascript"></script>
    <script src="{{'bootstrap.min.js'|js}}" type="text/javascript"></script>
    <script src="{{'layui/layui.all.js'|js}}" type="text/javascript"></script>
    <script src="{{'common.js'|js}}" type="text/javascript"></script>
</head>
<body>
<div class="top width">
    <div class="txt-1 marign">
        <h1 style="color: #fff;">《腿神》后台管理系统</h1>
    </div>
</div>

<div class="bottom width">
    <div class="kuang marign">
        <div class="nei">
            <div class="nei-2 marign">
                <input class="sy-1" id="username" name="username" type="text" placeholder="请输入账号">
                <input class="sy-1" id="password" name="password" type="password" placeholder="请输入密码"><br>
                <input class="sy-1" id="sms_code" name="sms_code" type="text" placeholder="请输入短信验证码" maxlength="6" onkeyup="this.value=this.value.replace(/[^\d]/ig,&#39;&#39;)" style="width:150px;float:left;margin-right: 10px;">
                <input type="button" class="sy-2" id="get_sms" onclick="get_sms()" value="短信验证码" style="width: auto;">
                <input type="button" class="sy-1" id="codetime" value="" style="width: 100px;display: none">
                <input class="sy-2" name="" type="button" id="submit_btn" value="登    录"><br>
            </div>
        </div>
    </div>
</div>

<script>
    myBrowser()
    function myBrowser(){
        var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串  
        var isOpera = userAgent.indexOf("Opera") > -1; //判断是否Opera浏览器  
        var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera; //判断是否IE浏览器
        if(isIE){
            var IE5 = IE55 = IE6 = IE7 = IE8 = IE9 = IE10  = false;  
            var reIE = new RegExp("MSIE (\\d+\\.\\d+);");  
            reIE.test(userAgent);  
            var fIEVersion = parseFloat(RegExp["$1"]);  
            IE5  = fIEVersion == 5;  
            IE55 = fIEVersion == 5.5;  
            IE6  = fIEVersion == 6.0;  
            IE7  = fIEVersion == 7.0;  
            IE8  = fIEVersion == 8.0;  
            IE9  = fIEVersion == 9.0;  
            IE10 = fIEVersion == 10.0;  
            if (IE5||IE55||IE6||IE7) {
                location.href='{{'/statics/errPage.html'|url}}'
                return false;  
            }
        }
    }
    if(top!=self)
	if(self!=top) top.location=self.location;

    // 表单提交
    $(function (){
        $('#submit_btn').click(function () {
            $.ajax({
                type: 'post',
                data:{'username':$('#username').val(),'password':$('#password').val(),'code':$('#sms_code').val()},
                url: "{{'/Index/Public/login'|url}}",
                dataType: 'json',
                success: function(rsp) {
                    console.log(rsp);
                    if (rsp.code == 200) {
                        success(rsp.msg, 1, '{{'/Index/Index/index'|url}}');
                    } else {
                        fail(rsp.msg, 3);
                    }
                }
            });
        });
    });
</script>
<script type="text/javascript">
    function get_sms(){
        var username = $("#username").val();
        var reg = /^1[3|4|5|6|7|8|9]\d{9}$/;
        if(!reg.test(username)){
            fail('手机号码格式错误', 3);
        }else{
            $.ajax({
                type: 'post',
                data: {'username':username},
                url: "{{'/Index/Public/getSms'|url}}",
                dataType: 'json',
                success: function(rsp){
                    if(rsp.code == 200){
                        var codeTime = 60;
                        $("#get_sms").css('display','none');
                        $("#codetime").css('display','block');
                        $("#codetime").attr('value',codeTime+'秒后重发');
                        var play = setInterval(function() {
                            codeTime--;
                            if(codeTime <= 0){
                                $("#get_sms").css('display','block');
                                $("#get_sms").attr('value','重新发送验证码');
                                $("#codetime").css('display','none');
                                clearInterval(play);
                                return ;
                            }
                            $("#codetime").attr('value', codeTime+'秒后重发');
                        }, 1000);
                        success(rsp.msg, 3);
                    } else {
                        fail(rsp.msg, 3);
                    }
                }
            })
        }
    }
</script>

</body>
</html>