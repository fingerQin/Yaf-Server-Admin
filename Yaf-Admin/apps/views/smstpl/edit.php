{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">模板标题：</th>
				<td>
					<input type="text" name="title" id="title" class="input-text" value="{{$detail.title}}" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">模板 KEY：</th>
				<td>
					<input type="text" name="send_key" id="send_key" class="input-text" value="{{$detail.send_key}}" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">触发类型：</th>
				<td>
					<select name="trigger_type" class="form-control" >
						<option {{if $detail.trigger_type==1}}selected="selected"{{/if}} value="1">用户触发</option>
						<option {{if $detail.trigger_type==2}}selected="selected"{{/if}} value="2">系统触发</option>
					</select>
				</td>
			</tr>
			<tr>
                <th class="left-txt">短信内容：</th>
                <td><textarea name="sms_body" id="sms_body" class="textarea" rows="3" cols="50">{{$detail.sms_body}}</textarea></td>
            </tr>
			<tr>
				<td></td>
				<td>
                    <input type="hidden" name="id" value="{{$detail.id}}" />
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
            url: '{{'SmsTpl/edit'|url}}',
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