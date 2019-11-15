{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">模板标题：</th>
				<td>
					<input type="text" name="title" id="title" class="input-text" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">模板 KEY：</th>
				<td>
					<input type="text" name="send_key" id="send_key" class="input-text" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">触发类型：</th>
				<td>
					<select name="trigger_type" class="form-control" >
						<option value="1">用户触发</option>
						<option value="2">系统触发</option>
					</select>
				</td>
			</tr>
			<tr>
                <th class="left-txt">短信内容：</th>
                <td><textarea name="sms_body" id="sms_body" class="textarea" rows="3" cols="50"></textarea></td>
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
            url: '{{'SmsTpl/add'|url}}',
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