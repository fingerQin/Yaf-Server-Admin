{{include file="common/header.php"}}

<link href="{{'kindeditor_custom.css'|css}}" rel="stylesheet" type="text/css">

<div id="helpstr">
{{$helpstr nofilter}}<br />
<button type="button" id="helpstr-button">编辑文档</button>
</div>

<div class="pad_10" id="helpstr-editor">
	<form action="" method="post" name="myform" id="myform">
		<table cellpadding="2" cellspacing="1" class="table_form" width="100%">
			<tr>
				<td>
                    <textarea name="content" id="editor_id" style="width: 95%; height: 480px;padding:10px;" rows="5" cols="50">{{$helpstr}}</textarea>
                </td>
			</tr>
			<tr>
				<td width="100%" align="center" colspan="2">
                    <input name="c" type="hidden" value="{{$c}}" />
                    <input name="a" type="hidden" value="{{$a}}" />
				    <input id="form_submit" type="button" name="dosubmit" class="btn_submit" value=" 提交 " />
			    </td>
			</tr>
		</table>
	</form>
</div>

<script charset="utf-8" src="{{'/kindeditor/kindeditor-all.js'|js}}"></script>
<script charset="utf-8" src="{{'/kindeditor/lang/zh-CN.js'|js}}"></script>
<script type="text/javascript">

var editor;
$(document).ready(function(){
	KindEditor.ready(function(K) {
	    editor = K.create('#editor_id', {
			'items': [ 'source', '|', 'preview', 'template', 'code', '|',
			'justifyleft', 'justifycenter', 'justifyright',
			'clearhtml', 'selectall', 'removeformat', '|', 
			'formatblock', 'bold', 'italic', 'underline', 'strikethrough', '|', 'image',
			'flash', 'media', 'insertfile', 'table', 'baidumap', 'pagebreak',
			'anchor', 'link', 'unlink', 'fullscreen'],
			'cssPath' : '{{'kindeditor_custom.css'|css}}',
			'uploadJson' : '{{'News/upload'|url}}',
			'allowFileManager' : false,
			'urlType' : 'domain'
		});
	});
	$('#form_submit').click(function(){
		editor.sync();
	    $.ajax({
	    	type: 'post',
            url: '{{'Index/setHelp'|url}}',
            dataType: 'json',
            data: $('form').eq(0).serialize(),
            success: function(data) {
                if (data.code == 200) {
                	window.location.reload();
                } else {
                	dialogTips(data.msg, 3);
                }
            }
	    });
    });
    $('#helpstr-button').click(function(){
        $('#helpstr').hide();
        $('#helpstr-editor').show();
    });
});
</script>

{{include file="common/footer.php"}}