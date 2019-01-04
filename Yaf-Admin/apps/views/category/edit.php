{{include file="common/header.php"}}

<div class="main">
	<form name="myform" id="myform" action="{{'Category/edit'|url}}" method="post">
		<table cellpadding="2" cellspacing="1" class="content" width="100%">
			<tr>
				<th class="left-txt">上级分类：</th>
				<td>
					<select name="parentid" class="form-control" disabled="disabled">
						<option value="0">作为一级分类</option>
						{{foreach $list as $menu}}
						<option {{if $menu.cat_id==$detail.parentid}}selected="selected"{{/if}} value="{{$menu.cat_id}}">{{$menu.cat_name}}</option>
						{{if isset($menu['sub'])}}
						{{foreach $menu.sub as $sub_m}}
						<option {{if $sub_m.cat_id==$detail.parentid}}selected="selected"{{/if}} value="{{$sub_m.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$sub_m.cat_name}}</option>
						{{if isset($sub_m.sub)}}
						{{foreach $sub_m.sub as $ss_m}}
						<option {{if $ss_m.cat_id==$detail.parentid}}selected="selected"{{/if}} value="{{$ss_m.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$ss_m.cat_name}}</option>
						{{/foreach}}
						{{/if}}
						{{/foreach}}
						{{/if}}
						{{/foreach}}
					</select>
					<div class="w90" style="color:grey;">(不可修改)</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">分类名称：</th>
				<td><input type="text" name="cat_name" id="cat_name" class="input-text" value="{{$detail.cat_name}}"></td>
			</tr>
			<tr>
				<th class="left-txt">分类类型：</th>
				<td>
					<select name="cat_type" disabled="disabled" class="form-control">
						{{foreach $cat_type_list as $type_id => $type_name}}
						<option {{if $type_id==$detail.cat_type}}selected="selected"{{/if}} value="{{$type_id}}">{{$type_name}}</option>
						{{/foreach}}
        			</select> 
					<div class="w90" style="color:grey;">(不可修改)</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">外部链接：</th>
				<td>
					<select name="is_out_url" class="form-control">
						<option {{if $detail.is_out_url==0}}selected="selected"{{/if}} value="0">否</option>
						<option {{if $detail.is_out_url==1}}selected="selected"{{/if}} value="1">是</option>
					</select>
				</td>
			</tr>
			<tr>
				<th class="left-txt">外部链接：</th>
				<td>
					<input type="text" name="out_url" id="out_url" class="input-text" value="{{$detail.out_url}}" />
				</td>
			</tr>
			<tr>
				<th class="left-txt">显示分类：</th>
				<td>
					<input {{if $detail.display}}checked="checked"{{/if}} type="radio" name="display" value="1"> 是 
					<input {{if !$detail.display}}checked="checked"{{/if}} type="radio" name="display" value="0"> 否
				</td>
			</tr>
			<tr>
				<td width="100%" align="center" colspan="2">
					<input type="hidden" name="cat_id" value="{{$detail.cat_id}}" /> 
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