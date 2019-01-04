{{include file="common/header.php"}}

<div class="main">
	<form name="myform" id="myform" action="{{'Category/add'|url}}" method="post">
		<table cellpadding="2" cellspacing="1" class="content" width="100%">
			<tr>
				<th class="left-txt">上级分类：</th>
				<td>
					<select name="parentid" class="form-control" {{if $parent_cat_info}}disabled="disabled"{{/if}}>
						<option value="0">作为一级分类</option>
						{{foreach $list as $menu}}
						<option {{if $menu.cat_id==$parentid}}selected="selected"{{/if}} value="{{$menu.cat_id}}">{{$menu.cat_name}}</option>
						{{if isset($menu['sub'])}}
						{{foreach $menu.sub as $sub_m}}
						<option {{if $sub_m.cat_id==$parentid}}selected="selected"{{/if}} value="{{$sub_m.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$sub_m.cat_name}}</option>
						{{if isset($sub_m.sub)}}
						{{foreach $sub_m.sub as $ss_m}}
						<option {{if $ss_m.cat_id==$parentid}}selected="selected"{{/if}} value="{{$ss_m.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$ss_m.cat_name}}</option>
						{{/foreach}}
						{{/if}}
						{{/foreach}}
						{{/if}}
						{{/foreach}}
						{{if $parentid > 0}}
						<input name="parentid" value="{{$parentid}}" type="hidden" />
						{{/if}}
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">分类名称：</th>
				<td><input type="text" name="cat_name" id="cat_name" class="input-text"></td>
			</tr>
			<tr>
				<th class="left-txt">分类类型：</th>
				<td>
					<select class="form-control" {{if $parent_cat_info}}disabled="disabled"{{/if}} id="cat_type" name="cat_type">
     				{{foreach $cat_type_list as $type_id => $type_name}}
					{{if $parent_cat_info}}
					<option {{if $type_id==$parent_cat_info.cat_type}}selected="selected"{{/if}} value="{{$type_id}}">{{$type_name}}</option>
					{{else}}
					<option {{if $type_id==$cat_type}}selected="selected"{{/if}} value="{{$type_id}}">{{$type_name}}</option>
					{{/if}}
            		{{/foreach}}
        			</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">外部链接：</th>
				<td>
					<select name="is_out_url" class="form-control">
						<option value="0">否</option>
						<option value="1">是</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">外部 URL：</th>
				<td>
					<input type="text" name="out_url" id="out_url" class="input-text" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">显示分类：</th>
				<td>
					<input type="radio" name="display" value="1" checked> 是 
					<input type="radio" name="display" value="0"> 否
				</td>
			</tr>
			<tr>
				<td width="100%" align="center" colspan="2">
					<input id="form_submit" type="button" name="dosubmit" class="btn btn-default" value=" 提交 " />
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
<!--

$(document).ready(function(){
	$('#form_submit').click(function(){
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