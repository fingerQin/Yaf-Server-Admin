{{include file="common/header.php"}}

<div class="main">
	<form action="{{'Config/edit'|url}}" method="post" name="myform" id="myform">
		<table cellpadding="2" cellspacing="1" class="content" width="100%">
			<tr>
				<th class="left-txt">配置标题：</th>
				<td>
					<input type="text" name="title" id="title" size="20" class="input-text" value="{{$detail.title|htmlspecialchars}}">
					<div class="w90" style="color:grey;">（简要说明）</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">配置编码：</th>
				<td>
					<input type="text" name="cfg_key" id="cfg_key" size="20" class="input-text" value="{{$detail.cfg_key|htmlspecialchars}}">
					<div class="w90" style="color:grey;">（字母、数字、下划线组成）</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">配置值：</th>
				<td>
					<textarea name="cfg_value" id="cfg_value" class="textarea" rows="3" cols="50">{{$detail.cfg_value|htmlspecialchars}}</textarea></td>
			</tr>
			<tr>
				<th class="left-txt">描述：</th>
				<td><textarea name="description" id="description" class="textarea" rows="3" cols="50">{{$detail.description|htmlspecialchars}}</textarea></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input name="configid" type="hidden" value="{{$detail.configid}}" /> 
					<span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
<!--

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

//-->
</script>

{{include file="common/footer.php"}}