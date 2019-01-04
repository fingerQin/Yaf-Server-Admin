{{include file="common/header.php"}}

<div class="main">
	<form name="myform" id="fromID" action="{{'Menu/edit'|url}}" method="post">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<th class="left-txt">上级菜单：</th>
					<td>
						<select name="parentid" class="form-control">
							<option value="0">作为一级菜单</option>
								{{foreach $menus as $menu}}
								<option
								{{if $menu.menuid == $detail.parentid}}selected="selected"{{/if}} value="{{$menu.menuid}}">{{$menu.menu_name}}</option>
								{{foreach $menu.sub as $sub_m}}
								<option {{if $sub_m.menuid == $detail.parentid}}selected="selected"{{/if}} value="{{$sub_m.menuid}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$sub_m.menu_name}}</option>
								{{foreach $sub_m.sub as $ss_m}}
								<option {{if $ss_m.menuid == $detail.parentid}}selected="selected"{{/if}} value="{{$ss_m.menuid}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$ss_m.menu_name}}</option>
								{{/foreach}}
							{{/foreach}}
							{{/foreach}}
						</select>
					</td>
				</tr>
				<tr>
					<th class="left-txt">菜单名称：</th>
					<td><input type="text" name="name" id="name" value="{{$detail.menu_name}}" class="input-text"></td>
				</tr>
				<tr>
					<th class="left-txt">文件名：</th>
					<td><input type="text" name="c" id="c" value="{{$detail.c}}" class="input-text" /></td>
				</tr>
				<tr>
					<th class="left-txt">方法名：</th>
					<td><input type="text" name="a" id="a" value="{{$detail.a}}" class="input-text" /> <span id="a_tip"></span></td>
				</tr>
				<tr>
					<th class="left-txt">菜单图标名：</th>
					<td>
						<input type="text" name="ico" name="ico" class="input-text" />
						<div class="w90" style="color:grey;">(菜单图标由前端指定并提交，再由后端根据前端指定的图标名进行输入保存)</div>
					</td>
				</tr>
				<tr>
					<th class="left-txt">附加参数：</th>
					<td><input type="text" name="data" value="{{$detail.ext_param}}" class="input-text" /></td>
				</tr>
				<tr>
					<th class="left-txt">是否显示菜单：</th>
					<td>
						<input {{if $detail.is_display}}checked="checked"{{/if}} type="radio" name="display" value="1"> 是 
						<input {{if !$detail.is_display}}checked="checked"{{/if}} type="radio" name="display" value="0"> 否
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="hidden" name="menu_id" value="{{$detail.menuid}}" />
						<span><input class="btn btn-default" id="form_submit" type="button" value="保存并提交"></span>
					</td>
				</tr>
			</tbody>
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