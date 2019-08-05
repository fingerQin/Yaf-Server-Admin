{{include file="common/header.php"}}

<div class="main">
    <form action={{'Ad/editAd'|url}} id="fromID">
        <table class="content" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <th class="left-txt">广告名称：</th>
                <td>
                    <input type="text" name="ad_name" id="ad_name" size="20" class="input-text" value="{{$detail.ad_name}}">
                </td>
            </tr>
            <tr>
                <th class="left-txt">生效时间：</th>
                <td>
                    <input type="text" name="start_time" id="start_time" size="20" class="date input-text" value="{{$detail.start_time}}">
                </td>
            </tr>
            <tr>
                <th class="left-txt">失效时间：</th>
                <td>
                    <input type="text" name="end_time" id="end_time" size="20" class="date input-text" value="{{$detail.end_time}}">
                </td>
            </tr>
            <tr>
                <th class="left-txt">显示状态：</th>
                <td>
                    <select name="display" class="form-control w40">
                        <option {{if $detail.display}}selected="selected"{{/if}} value="1">是</option>
                        <option {{if !$detail.display}}selected="selected"{{/if}} value="0">否</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th class="left-txt">广告URL：</th>
                <td>
                    <input type="text" name="ad_url" id="ad_url" size="30" class="input-text" value="{{$detail.ad_url}}">
                </td>
            </tr>
            <tr>
                <th class="left-txt">显示终端：</th>
                <td>
                    {{foreach $terminalDict as $terminal => $terminalLabel}}
                    <label style="margin-right:10px;padding-right:5px;">
                    <input type="checkbox" {{if ($terminal & $detail.terminal) == $terminal}}checked="checked"{{/if}} name="terminal[]" value="{{$terminal}}" />{{$terminalLabel}}</label>
                    {{/foreach}}
                </td>
            </tr>
            <tr>
                <th class="left-txt">广告图片：</th>
                <td>
                    <input type="hidden" name="ad_image_url" id="input_voucher" value="{{$detail.ad_image_url}}" />
                    <div id="previewImage"></div>
                </td>
            </tr>
            <tr>
                <th class="left-txt">高清广告图片：</th>
                <td>
                    <input type="hidden" name="ad_ipx_image_url" id="ipx_input_voucher" value="{{$detail.ad_ipx_image_url}}" />
                    <div id="ipx_previewImage"></div>
                </td>
            </tr>
            <tr>
                <th class="left-txt">备注：</th>
                <td>
                    <textarea name="remark" id="remark" class="form-control" rows="3" cols="50">{{$detail.remark|escape}}</textarea>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="hidden" name="ad_id" value="{{$detail.ad_id}}" />
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
uploadImage(filUrl, baseJsUrl, 'previewImage', 'input_voucher', 160, 120, uploadUrl, '4x3');
uploadImage(filUrl, baseJsUrl, 'ipx_previewImage', 'ipx_input_voucher', 160, 120, uploadUrl, '4x3');

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