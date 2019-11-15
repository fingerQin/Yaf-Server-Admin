{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>菜单列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				{{if 'Menu'|access:'add'}}
				<a type="button" class="btn btn-mystyle btn-sm" href="javascript:void(0)" onclick="add(0);">添加菜单</a>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Menu', 'index');">帮助</button>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="table-margin">
			<form name="myform" action="{{'Menu/sort'|url}}" method="post">
				<div class="table-margin">
					<table class="table table-bordered table-header">
						<thead>
							<tr>
								<th class="w5 text-center">排序</th>
								<th class="w5 text-center">id</th>
								<th class="w15 text-left">菜单名称</th>
								<th class="w15 text-left">资源名称</th>
								<th class="w10 text-center">显示状态</th>
								<th class="w10 text-center">修改时间</th>
								<th class="w10 text-center">创建时间</th>
								<th class="w15 text-center">管理操作</th>
							</tr>
						</thead>
						<tbody>
							{{foreach $list as $menu}}
							<tr>
							<td align='center'>
								<input name='listorders[{{$menu.menuid}}]' type='text' size='3' value='{{$menu.listorder}}' class='text-center input-text-c'>
							</td>
							<td align='center'>{{$menu.menuid}}</td>
							<td>{{$menu.menu_name}}</td>
							<td>{{$menu.c}}/{{$menu.a}}</td>
							<td align='center'>{{if $menu.is_display}}显示{{else}}隐藏{{/if}}</td>
							<td align='center'>{{$menu.u_time}}</td>
							<td align='center'>{{$menu.c_time}}</td>
							<td align='center'>
								{{if 'Menu'|access:'add'}}
								<a href="javascript:void(0);" onclick="add({{$menu.menuid}});">添加子菜单</a> |
								{{/if}} 

								{{if 'Menu'|access:'edit'}}
								<a href="javascript:void(0);" onclick="edit({{$menu.menuid}}, '{{{{$menu.menu_name}}}}');">修改</a>
								| 
								{{/if}}

								{{if 'Menu'|access:'delete'}}
								<a href="javascript:void(0)" class="deleteMenu" rel="{{$menu.menuid}}">删除</a>
								{{/if}}
							</td>
							</tr>
							{{foreach $menu.sub as $sub_m}}
							<tr>
								<td align='center'>
									<input name='listorders[{{$sub_m.menuid}}]' type='text' size='3' value='{{$sub_m.listorder}}' class='text-center input-text-c'>
								</td>
								<td align='center'>{{$sub_m.menuid}}</td>
								<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─ {{$sub_m.menu_name}}</td>
								<td>{{$sub_m.c}}/{{$sub_m.a}}</td>
								<td align='center'>{{if $sub_m.is_display}}显示{{else}}隐藏{{/if}}</td>
								<td align='center'>{{$sub_m.u_time}}</td>
								<td align='center'>{{$sub_m.c_time}}</td>
								<td align='center'>
									{{if 'Menu'|access:'add'}}
									<a href="javascript:void(0);" onclick="add({{$sub_m.menuid}});">添加子菜单</a>
									| 
									{{/if}}
									{{if 'Menu'|access:'edit'}}
									<a href="javascript:void(0);" onclick="edit({{$sub_m.menuid}}, '{{{{$sub_m.menu_name}}}}');">修改</a>
									| 
									{{/if}}
									{{if 'Menu'|access:'delete'}}
									<a href="javascript:void(0)" class="deleteMenu" rel="{{$sub_m.menuid}}">删除</a>
									{{/if}}
								</td>
							</tr>
							{{foreach $sub_m.sub as $ss_m}}
							<tr>
							<td align='center'>
								<input name='listorders[{{$ss_m.menuid}}]' type='text' size='3' value='{{$ss_m.listorder}}' class='text-center input-text-c'>
							</td>
							<td align='center'>{{$ss_m.menuid}}</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─ {{$ss_m.menu_name}}</td>
							<td>{{$ss_m.c}}/{{$ss_m.a}}</td>
							<td align='center'>{{if $ss_m.is_display}}显示{{else}}隐藏{{/if}}</td>
							<td align='center'>{{$ss_m.u_time}}</td>
							<td align='center'>{{$ss_m.c_time}}</td>
							<td align='center'>
								{{if 'Menu'|access:'add'}}
								<a href="javascript:void(0);" onclick="add({{$ss_m.menuid}});">添加子菜单</a>
								| 
								{{/if}}
								{{if 'Menu'|access:'edit'}}
								<a href="javascript:void(0);" onclick="edit({{$ss_m.menuid}}, '{{$ss_m.menu_name}}');">修改</a>
								| 
								{{/if}}
								{{if 'Menu'|access:'delete'}}
								<a href="javascript:void(0)" class="deleteMenu" rel="{{$ss_m.menuid}}">删除</a>
								{{/if}}
							</td>
							</tr>
							{{foreach $ss_m.sub as $sssub}}
							<tr>
							<td align='center'>
								<input name='listorders[{{$sssub.menuid}}]' type='text' size='3' value='{{$sssub.listorder}}' class='text-center input-text-c'>
							</td>
							<td align='center'>{{$sssub.menuid}}</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─ {{$sssub.menu_name}}</td>
							<td>{{$sssub.c}}/{{$sssub.a}}</td>
							<td align='center'>{{if $sssub.is_display}}显示{{else}}隐藏{{/if}}</td>
							<td align='center'>{{$sssub.u_time}}</td>
							<td align='center'>{{$sssub.c_time}}</td>
							<td align='center'>
								{{if 'Menu'|access:'add'}}
								<a href="javascript:void(0);" onclick="add({{$sssub.menuid}});">添加子菜单</a>
								| 
								{{/if}}
								{{if 'Menu'|access:'edit'}}
								<a href="javascript:void(0);" onclick="edit({{$sssub.menuid}}, '{{$sssub.menu_name}}');">修改</a>
								| 
								{{/if}}
								{{if 'Menu'|access:'delete'}}
								<a href="javascript:void(0)" class="deleteMenu" rel="{{$sssub.menuid}}">删除</a>
								{{/if}}
							</td>
							</tr>
							{{/foreach}}
							{{/foreach}}
							{{/foreach}}
						{{/foreach}}
						</tbody>
					</table>
				</div>
			</form>
			{{if 'Menu'|access:'sort'}}
			<div class="btn">
				<input type="button" id="form_submit" class="button" name="dosubmit" value="排序" />
			</div>
			{{/if}}
		</div>
	</div>
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
					success(data.msg, 1, '');
                } else {
					dialogTips(data.msg, 3);
                }
            }
	    });
	});

	$('.deleteMenu').click(function () {
		var id = $(this).attr('rel');
		layer.confirm('您确定要删除吗？', {
			title: "操作提示",
			btn: ['确定', '取消'] //按钮
		}, function() {
			$.ajax({
				type: 'post',
				data: {'menu_id': id},
				url: "{{'Menu/delete'|url}}",
				dataType: 'json',
				success: function (rsp) {
					if (rsp.code == 200) {
						dialogTips(rsp.msg, 1);
						window.location.reload();
					} else {
						dialogTips(rsp.msg, 3);
					}
				}
			});
		}, function(){
			return true;
		});
	});
});

function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Menu/edit'|url}}?menu_id=" + id;
	postDialog('editType', page_url, title, 400, 440);
}

function add(id) {
	postDialog('addMenu', '{{'Menu'|url:'add'}}?parentid=' + id, '添加菜单', 400, 440);
}

//-->
</script>

{{include file="common/footer.php"}}