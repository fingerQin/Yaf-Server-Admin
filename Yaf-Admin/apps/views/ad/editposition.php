{{include file="common/header.php"}}

<div class="main">
    <form action={{'Ad/editPosition'|url}} id="fromID">
        <table class="content" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <th class="left-txt" style="width:25%">广告位名称：</th>
                <td>
                    <input type="text" name="pos_name" id="pos_name" size="20" class="input-text" style="width:90%" value="{{$detail.pos_name}}">
                </td>
            </tr>
            <tr>
                <th class="left-txt">广告位编码：</th>
                <td>
                    <input type="text" name="pos_code" id="pos_code" size="20" class="input-text" style="width:90%" value="{{$detail.pos_code}}">
                </td>
            </tr>
            <tr>
                <th class="left-txt">可展示数量：</th>
                <td>
                    <input type="text" name="pos_ad_count" id="pos_ad_count" size="5" class="input-text" style="width:90%" value="{{$detail.pos_ad_count}}">
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="hidden" name="pos_id" value="{{$detail.pos_id}}" />
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
            dataType: 'json',
            url: $('form').eq(0).attr('action'),
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