{{include file="common/header.php"}}
    <div class="container-fluid">
        <div class="info-center">
            <div class="page-header">
                <div class="pull-left">
                    <h4>修改密码</h4>
                </div>
            </div>
            <div class="clearfix"></div>

            <div class="main" style="width:560px;margin:120px auto;vertical-align: middle;">
                <form name="myform" id="fromID">
		            <table cellpadding="2" cellspacing="1" class="content" width="100%">
                        <tbody>
                        <tr>
                            <th class="left-txt" style="width:120px;"><span class="c-red">*</span>真实姓名：</td>
                            <td>{{$admin_info.real_name}}</td>
                        </tr>
                        <tr>
                            <th class="left-txt"><span class="c-red">*</span>手机号码：</td>
                            <td>{{$admin_info.mobile}}</td>
                        </tr>
                        <tr>
                            <th class="left-txt"><span class="c-red">*</span>旧密码：</td>
                            <td><input class="input-text" id="old_pwd" name="old_pwd" type="password"></td>
                        </tr>
                        <tr>
                            <th class="left-txt"><span class="c-red">*</span>新密码：</td>
                            <td>
                                <input type="password" name="new_pwd" id="new_pwd" size="20" class="input-text" value="">
                            </td>
                        </tr>
                        <tr>
                            <th class="left-txt"><span class="c-red">*</span>确认密码：</td>
                            <td>
                                <input type="password" name="confirm_pwd" id="confirm_pwd" size="20" class="input-text" value="">
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <span><input class="btn btn-default" id="submitID" name="" type="button" value="保存并提交"></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>

        </div>
    </div>
    <script type="text/javascript">
        //表单提交
        $(function (){
            $('#submitID').click(function () {
                var fromData = $('#fromID').serialize();
                var new_pwd = $('#new_pwd').val();
                var confirm_pwd = $('#confirm_pwd').val();
                if (new_pwd != confirm_pwd) {
                    fail('新密码与确认密码不相同', 3);
                    return false;
                }
                $.ajax({
                    type: 'post',
                    data:fromData,
                    url: "{{'admin/editPwd'|url}}",
                    dataType: 'json',
                    success: function(rsp) {
                        if (rsp.code == 200) {
                            success(rsp.msg, 2, "{{'public/login'|url}}");
                        } else {
                            fail(rsp.msg, 3);
                        }
                    }
                });
            });
        });
    </script>

{{include file="common/footer.php"}}