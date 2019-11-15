{{include file="common/header.php"}}

<div class="main">
    <form id="fromID">
        <input type="hidden" name="roleid" value="{{$role.roleid}}"/>
        <table class="content" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <th class="left-txt">角色名称：</th>
                <td><input type="text" name="rolename" class="input-text" value="{{$role.role_name}}"></td>
            </tr>
            <tr>
                <th class="left-txt">排序：</th>
                <td>
                    <input type="text" name="listorder" class="input-text" value="{{$role.listorder}}">
                    <div class="w90" style="color:grey;">（小在前）</div>
                </td>
            </tr>
            <tr>
                <th class="left-txt">角色说明：</th>
                <td><textarea rows="3" cols="50" name="description" class="textarea">{{$role.description}}</textarea></td>
            </tr>
            <tr>
                <td></td>
                <td>
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
                data:fromData,
                dataType:'json',
                url: "{{'Role/edit'|url}}",
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