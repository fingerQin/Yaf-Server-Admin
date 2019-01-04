{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>角色列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				{{if 'Role'|access:'add'}}
				<a type="button" class="btn btn-mystyle btn-sm" href="javascript:void(0)" onclick="add()">添加角色</a>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Role', 'index');">帮助</button>
			</div>
		</div>

		<div class="clearfix"></div>
			<div class="table-margin">
				<table class="table table-bordered table-header">
					<thead>
						<tr>
							<th class="w15 text-center">角色ID</th>
							<th class="w15 text-center">角色名称</th>
							<th class="w15 text-center">角色说明</th>
							<th class="w15 text-center">创建时间</th>
							<th class="w15 text-center">管理操作</th>
						</tr>
					</thead>
					<tbody>
						{{foreach $roles as $role}}
    					<tr>
							<td class="text-center">{{$role.roleid}}</td>
							<td class="text-center">{{$role.role_name}}</td>
							<td class="text-center">{{$role.description}}</td>
							<td class="text-center">{{$role.c_time}}</td>
							<td class="text-center">
								{{if 'Role'|access:'edit'}}
								<a class="custom-a" href="javascript:void(0);" onclick="edit({{$role.roleid}}, '{{$role.role_name}}');">编辑</a>
								{{/if}}
								{{if 'Role'|access:'setPermission'}}
								<a class="custom-a" href="{{'Role'|url:'getRolePermissionMenu'}}?roleid={{$role.roleid}}">角色权限管理</a>
								{{/if}}
								{{if 'Role'|access:'delete'}}
								<a class="del" rel="{{$role.roleid}}" href="javascript:void(0)">删除</a>
								{{/if}}
							</td>
						</tr>
    				{{/foreach}}
				</tbody>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
	//表单提交
	$(function () {
		$('tbody tr td .del').click(function () {
			var id = $(this).attr('rel');
			layer.confirm('您确定要删除吗？', {
				title: "操作提示",
				btn: ['确定', '取消'] //按钮
			}, function() {
				$.ajax({
					type: 'post',
					data: {'roleid': id},
					url: "{{'Role/delete'|url}}",
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
		$('.reload').click(function(){
			window.location.reload()
		})
	});
	
	function edit(id, name) {
		var title = '修改『' + name + '』';
		var page_url = "{{'Role/edit'|url}}?roleid=" + id;
		postDialog('editRoleEdit', page_url, title, 400, 300);
	}
	function add() {
		postDialog('addRoleEdit', '{{'Role'|url:'add'}}', '添加角色', 400, 300);
	}
</script>

{{include file="common/footer.php"}}