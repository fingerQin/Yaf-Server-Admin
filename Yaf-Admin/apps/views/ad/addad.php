{{include file="common/header.php"}}

<div class="main">
    <form action={{'Ad/addAd'|url}} id="fromID">
        <table class="content" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <th class="left-txt">广告名称：</th>
                <td>
                    <input type="text" name="ad_name" id="ad_name" size="20" class="input-text" value="">
                </td>
            </tr>
            <tr>
                <th class="left-txt">生效时间：</th>
                <td>
                    <input type="text" name="start_time" id="start_time" size="20" class="date input-text" value="">
                </td>
            </tr>
            <tr>
                <th class="left-txt">失效时间：</th>
                <td>
                    <input type="text" name="end_time" id="end_time" size="20" class="date input-text" value="">
                </td>
            </tr>
            <tr>
                <th class="left-txt">显示状态：</th>
                <td>
                    <select name="display" class="form-control w40">
                        <option value="1">是</option>
                        <option value="0">否</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th class="left-txt">广告URL：</th>
                <td>
                    <input type="text" name="ad_url" id="ad_url" size="30" class="input-text" value="">
                </td>
            </tr>
            <tr>
                <th class="left-txt">友情链接图片：</th>
                <td>
                    <input type="hidden" name="ad_image_url" id="input_voucher" value="" />
                    <div id="previewImage"></div>
                </td>
            </tr>
            <tr>
                <th class="left-txt">备注：</th>
                <td>
                    <textarea name="remark" id="remark" class="form-control" rows="3" cols="50"></textarea>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="hidden" name="pos_id" value="{{$pos_id}}" /> 
                    <span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
                </td>
            </tr>
        </table>
    </form>
</div>

<script src="{{'/AjaxUploader/uploadImage.js'|js}}"></script>
<script type="text/javascript">
<!--

var uploadUrl = '{{'Index/Upload'|url}}';
var baseJsUrl = '{{''|js}}';
var filUrl    = '{{$files_domain_name}}';
uploadImage(filUrl, baseJsUrl, 'previewImage', 'input_voucher', 120, 120, uploadUrl);

Calendar.setup({
	weekNumbers: false,
    inputField : "start_time",
    trigger    : "start_time",
    dateFormat: "%Y-%m-%d %H:%I:%S",
    showTime: true,
    minuteStep: 1,
    onSelect   : function() {this.hide();}
});

Calendar.setup({
	weekNumbers: false,
    inputField : "end_time",
    trigger    : "end_time",
    dateFormat: "%Y-%m-%d %H:%I:%S",
    showTime: true,
    minuteStep: 1,
    onSelect   : function() {this.hide();}
});

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