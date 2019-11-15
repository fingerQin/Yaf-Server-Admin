{{include file="common/header.php"}}

<div class="main">
	<form name="myform" id="myform" action="{{'Link/edit'|url}}" method="post">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">上级菜单：</th>
				<td>
					<select name="cat_id" class="form-control">
						{{foreach $cat_list as $menu}}
						<option {{if $menu.cat_id == $detail.cat_id}}selected="selected"{{/if}} value="{{$menu.cat_id}}">{{$menu.cat_name}}</option>
						{{if isset($menu['sub'])}}
						{{foreach $menu['sub'] as $sub_m}}
						<option {{if $menu.cat_id == $detail.cat_id}}selected="selected"{{/if}} value="{{$sub_m.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$sub_m.cat_name}}</option>
						{{if isset($sub_m['sub'])}}
						{{foreach $sub_m.sub as $ss_m}}
						<option {{if $menu.cat_id == $detail.cat_id}}selected="selected"{{/if}} value="{{$ss_m.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─<?php echo $ss_m['cat_name']; ?></option>
						{{/foreach}}
						{{/if}}
						{{/foreach}}
						{{/if}}
						{{/foreach}}
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">友情链接名称：</th>
				<td>
					<input type="text" name="link_name" id="link_name" value="{{$detail.link_name|htmlspecialchars}}" class="input-text">
				</td>
			</tr>
			<tr>
				<th class="left-txt">友情链接URL：</th>
				<td>
					<input type="text" name="link_url" id="link_url" class="input-text" value="{{$detail.link_url|htmlspecialchars}}" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">是否显示：</th>
				<td>
					<select name="display" class="form-control">
						<option {{if $menu.display}}selected="selected"{{/if}} value="1">是</option>
						<option {{if !$menu.display}}selected="selected"{{/if}} value="0">否</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">友情链接图片：</th>
				<td>
					<input type="hidden" name="image_url" id="input_voucher" value="{{$detail.image_url}}" />
					<div id="previewImage"><img style="max-width: 119px; max-height: 119px;" src="{{$detail.image_url}}" /></div>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="hidden" name="link_id" value="{{$detail.link_id}}" /> 
					<span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
				</td>
			</tr>
		</table>
	</form>
</div>

<script src="{{'AjaxUploader/uploadImage.js'|js}}"></script>
<script type="text/javascript">

var uploadUrl = '{{'Index/Upload'|url}}';
var baseJsUrl = '{{''|js}}';
var filUrl    = '{{$files_domain_name}}';
uploadImage(filUrl, baseJsUrl, 'previewImage', 'input_voucher', 120, 120, uploadUrl);

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

</script>

{{include file="common/footer.php"}}