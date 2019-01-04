{{include file="common/header.php"}}

<div class="main">
	<form id="fromID" action="{{'User/status'|url}}">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">状态</th>
				<td>
                    <label>禁用&nbsp;<input type="radio" name="status" value="2" /></label>
                    <label style="margin-left:20px;">冻结&nbsp;<input type="radio" name="status" value="3" /></label>
                    <div class="w90" style="color:grey;">禁用是禁止账号登录,冻结是冻结用户的资金/金币变动</div>
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
                    success(data.msg, 2, 'parent');
                } else {
                    fail(data.msg, 3);
                }
            }
	    });
	});
});
</script>

{{include file="common/footer.php"}}