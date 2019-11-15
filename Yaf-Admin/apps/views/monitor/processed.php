{{include file="common/header.php"}}

<div class="main">
	<form action={{'Monitor/processed'|url}} method="post" name="myform" id="myform">
		<table cellpadding="2" cellspacing="1" class="content" width="100%">
			<tbody>
				<tr>
					<th class="left-txt">ID：</th>
					<td>{{$id}}</td>
				</tr>
				<tr>
					<th class="left-txt">操作备注</th>
					<td>
                        <textarea name="remark" style="height:120px;width:280px;"></textarea>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
                        <span><input class="btn btn-default" id="submitID" type="button" value="确定"></span>
                        <input name="id" type="hidden" value="{{$id}}" />
					</td>
				</tr>
			<tbody>
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