{{include file="common/header.php"}}

<div class="main">
    <form id="fromID" style="padding-left:40px;">
        <table class="content" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <textarea name="mobiles" id="mobiles" class="textarea" rows="3" cols="50" style="height:250px;"></textarea>
                    <div class="w90" style="color:grey;text-align:center">(格式：13812341234，每行一个手机号码)</div>
                </td>
            </tr>
            <tr>
                <td style="text-align:center">
                    <span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
                </td>
            </tr>
        </table>
    </form>
</div>

<script type="text/javascript">
    //表单提交
    $(function (){
        $('#submitID').click(function () {
            var fromData = $('#fromID').serialize();
            $.ajax({
                type: 'post',
                data: fromData,
                dataType:'json',
                url: "{{'Sms/addblist'|url}}",
                success: function(rsp) {
                    if (rsp.code == 200) {
                        success(rsp.msg, 1, 'parent');
                    } else {
                        fail(rsp.msg, 3);
                    }
                }
            });
        });
    });
</script>

{{include file="common/footer.php"}}