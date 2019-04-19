{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">公告标题：</th>
				<td>
					<input type="text" name="title" id="title" class="input-text" />
				</td>
            </tr>
            <tr>
                <th class="left-txt">公告摘要：</th>
                <td><textarea name="summary" id="summary" class="textarea" rows="3" cols="50"></textarea></td>
            </tr>
			<tr>
                <th class="left-txt">公告内容：</th>
                <td><textarea name="body" id="body" class="textarea" rows="3" cols="50"></textarea></td>
            </tr>
            <tr>
                <th class="left-txt">所属终端：</th>
                <td>
                    {{foreach $terminal as $val => $name}}
                    <label style="margin-right:20px;"><input type="checkbox" name="terminal" class="terminal" value="{{$val}}" />{{$name}}</label>
                    {{/foreach}}
                </td>
            </tr>
			<tr>
				<td></td>
				<td>
                    <input type="hidden" name="terminal" id="platform" value="0">
					<span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
$(".terminal").on('click',function(){
    var platform_value = [];
    if(this.checked){
        $(".terminal:checked").each(function(k,v){
            var platform_name = $(this).attr('value');
            platform_value.push(platform_name);
        })
    }else{
        $(".terminal:checked").each(function(k,v){
            var platform_name = $(this).attr('value');
            platform_value.push(platform_name);
        })
    }
    var platform_list = platform_value.toString();
    $("#platform").attr('value', platform_list);
});

$(document).ready(function(){
	$('#submitID').click(function(){
	    $.ajax({
	    	type: 'post',
            url: '{{'Notice/add'|url}}',
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