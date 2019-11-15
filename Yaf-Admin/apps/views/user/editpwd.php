{{include file="common/header.php"}}

<div class="main">
	<form id="fromID" action="{{'User/editPwd'|url}}">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">新密码</th>
				<td>
					<input type="password" name="password" id="password" size="20" class="form-control" value="">
					<div class="w90" style="color:grey;">（字母、数字、下划线、破折号组成,且长度在6~20个字符之）</div>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="hidden" name="userid" value="{{$userid}}" />
					<span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
$(document).ready(function(){
	$('#submitID').click(function(){
	    $.ajax({
	    	type: 'post',
            url: $('form').eq(0).attr('action'),
            dataType: 'json',
            data: $('form').eq(0).serialize(),
            success: function(data) {
                if (data.code == 200) {
					success(data.msg, 1, 'parent');
                } else {
					fail(data.msg, 3);
                }
            }
	    });
	});
});
</script>

{{include file="common/footer.php"}}