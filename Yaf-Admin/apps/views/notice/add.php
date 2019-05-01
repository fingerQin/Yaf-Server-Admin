{{include file="common/header.php"}}
<style type="text/css">
.content .left-txt {
    width: 18%;
}
</style>
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
                <td><textarea name="body" id="editor_id" class="textarea" rows="3" cols="50"></textarea></td>
            </tr>
            <tr>
                <th class="left-txt">所属终端：</th>
                <td>
                    {{foreach $terminal as $val => $name}}
                    <label style="margin-right:20px;"><input type="checkbox" class="terminal" value="{{$val}}" />{{$name}}</label>
                    {{/foreach}}
                </td>
            </tr>
			<tr>
				<td></td>
				<td>
                    <input type="hidden" name="terminal" id="platform" value="0">
					<span><input class="btn btn-default" id="form_submit" type="button" value="保存并提交"></span>
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
			'uploadJson' : '{{'Notice/upload'|url}}',
			'allowFileManager' : false,
			'urlType' : 'domain',
            'width' : '554px',
            'minWidth' : '500px',
            'height' : '400px'
		});
	});
	$('#form_submit').click(function(){
		editor.sync();
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

</script>

{{include file="common/footer.php"}}