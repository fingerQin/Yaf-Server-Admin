{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>文章编辑</h4>
			</div>
			<div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="window.history.back();">返回</button>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="table-margin">

			<form action="{{'News/edit'|url}}" method="post" name="myform" id="myform">
				<table cellpadding="2" cellspacing="1" class="content" width="100%">
					<tr>
						<th width="100">文章标题：</th>
						<td><input type="text" name="title" id="title" size="40" class="form-control" style="width:80%" value="{{$detail.title|htmlspecialchars}}">(不得超过100个字符)</td>
					</tr>
					<tr>
						<th>分类</th>
						<td>
							<select id="parentCatId" class="form-control" style="width:200px;display:inline;">
								<option value="">请选择父分类</option>
								{{foreach $news_cat_list as $cat}}
								<option {{if $detail.parent_id==$cat.cat_id}}selected="selected"{{/if}} value="{{$cat.cat_id}}">{{$cat.cat_name}}</option>
								{{/foreach}}
							</select>
							<select id="subCatId" name="cat_code" class="form-control" style="width:200px;display:inline;">
								<option value="">请选择子分类</option>
							</select>
						</td>
					</tr>
					<tr>
						<th width="100">关键词：</th>
						<td><input type="text" name="keywords" id="keywords" size="40" class="form-control" style="width:300px;" value="{{$detail.keywords|htmlspecialchars}}"></td>
					</tr>
					<tr>
						<th width="100">文章简介：</th>
						<td><textarea name="intro" id="intro" style="width: 80%" rows="5" cols="50">{{$detail.intro|htmlspecialchars}}</textarea></td>
					</tr>
					<tr>
						<th width="100">来源：</th>
						<td><input type="text" name="source" id="source" size="20" class="form-control" style="width:300px;" value="{{$detail.keywords}}"></td>
					</tr>
					<tr>
						<th width="100">显示状态：</th>
						<td>
							<select name="display" class="form-control" style="width:100px;">
								<option {{if $detail.display==1}}selected="selected"{{/if}} value="1">显示</option>
								<option {{if $detail.display==0}}selected="selected"{{/if}}value="0">隐藏</option>
							</select>
						</td>
					</tr>
					<tr>
						<th width="100">文章主图：</th>
						<td>
							<input type="hidden" name="image_url" id="input_voucher" value="{{$detail.image_url}}" />
							<div id="previewImage">
							<img width="100" height="100" src="{{$detail.image_url}}" />
							</div>
						</td>
					</tr>
					<tr>
						<th width="100">文章内容：</th>
						<td><textarea name="content" id="editor_id" style="width: 80%; height: 400px;" rows="5" cols="50">{{$detail.content|htmlspecialchars}}</textarea></td>
					</tr>
					<tr>
						<td width="100%" align="center" colspan="2">
							<input id="form_submit" type="button" name="dosubmit" class="btn btn-default" value=" 提交 " />
							<input type="hidden" name="news_id" value="{{$detail.news_id}}" />
						</td>
					</tr>
				</table>

			</form>
		</div>
	</div>
</div>

<script charset="utf-8" src="{{'/kindeditor/kindeditor-all.js'|js}}"></script>
<script charset="utf-8" src="{{'/kindeditor/lang/zh-CN.js'|js}}"></script>
<script src="{{'/AjaxUploader/uploadImage.js'|js}}"></script>
<script type="text/javascript">

var uploadUrl = '{{'Index/Upload'|url}}';
var baseJsUrl = '{{''|js}}';
var filUrl    = '{{$files_domain_name}}';
uploadImage(filUrl, baseJsUrl, 'previewImage', 'input_voucher', 160, 120, uploadUrl, '4x3');
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
			'uploadJson' : '{{'News/Upload'|url}}',
			'allowFileManager' : false,
			'urlType' : 'domain'
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
					success(data.msg, 1, '{{'News/index'|url}}');
                } else {
					fail(data.msg, 3);
                }
            }
	    });
	});

	function getCategoryList(cat_id, cat_type, selectedCatCode)
	{
		$.ajax({
	    	type: 'post',
            url: '{{'Category/getListJson'|url}}',
            dataType: 'json',
            data: {"cat_type" : cat_type, "cat_id" : cat_id},
            success: function(data) {
                if (data.code == 200) {
					html = '<option value="">请选择子分类</option>';
                	$.each(data.data, function(key, val) {
						if (selectedCatCode.length > 0 && selectedCatCode == val.cat_code) {
							html += '<option selected="selected" value="' + val.cat_code + '">' + val.cat_name + '</option>';
						} else {
							html += '<option value="' + val.cat_code + '">' + val.cat_name + '</option>';
						}
					});
					$('#subCatId').empty();
					$('#subCatId').html(html);
                } else {
                	// ......
                }
            }
	    });
	}

	$('#parentCatId').change(function() {
		getCategoryList(this.value, 1, '');
	});
	// 默认加载。
	defaultParentId = $('#parentCatId').val();
	if (defaultParentId > 0) {
		getCategoryList(defaultParentId, 1, '{{$detail.cat_code}}');
	}
});
</script>

{{include file="common/footer.php"}}