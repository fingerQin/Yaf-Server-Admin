{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
            <tr>
				<th class="left-txt">手机账号：</th>
				<td>
					<input type="text" name="mobile" id="mobile" class="input-text" />
				</td>
            </tr>
            <tr>
				<th class="left-txt">消息类型：</th>
				<td>
					<select name="msg_type" class="form-control" >
						<option value="1">系统消息</option>
						<option value="2">福利消息</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">消息标题：</th>
				<td>
					<input type="text" name="title" id="title" class="input-text" />
				</td>
            </tr>
            <tr>
				<th class="left-txt">跳转 URL：</th>
				<td>
					<input type="text" name="url" id="url" class="input-text" />
				</td>
			</tr>
			<tr>
                <th class="left-txt">消息内容：</th>
                <td><textarea name="content" style="height:200px;" id="content" class="textarea" rows="3" cols="50"></textarea></td>
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

$(document).ready(function(){
	$('#submitID').click(function(){
	    $.ajax({
	    	type: 'post',
            url: '{{'Message/send'|url}}',
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