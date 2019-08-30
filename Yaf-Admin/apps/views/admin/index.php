{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>管理员列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				{{if 'Admin'|access:'add'}}
				<a type="button" class="btn btn-mystyle btn-sm" href="javascript:void(0);" onclick="add();">添加管理员</a>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Admin', 'index');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Admin/index'|url}}" method="post">
					<div class="form-group">
						<input type="text" name="keywords" class="form-control" style="width: 180px;" value="{{$keywords}}" placeholder="管理员姓名或手机">
					</div>
					<div class="form-group">
						<button type="submit" class="form-control btn btn-info"><span class="glyphicon glyphicon-search"></span> 查询</button>
					</div>
				</form>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="table-margin">
			<table class="table table-bordered table-header">
				<thead>
					<tr>
						<th class="w5 text-center">管理员ID</th>
						<th class="w15 text-center">真实姓名</th>
						<th class="w15 text-center">手机号码</th>
						<th class="w15 text-center">角色名称</th>
						<th class="w5 text-center">账号状态</th>
						<th class="w15 text-center">更新时间</th>
						<th class="w15 text-center">创建时间</th>
						<th class="w15 text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
					{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.adminid}}</td>
						<td class="text-center">{{$item.real_name}}</td>
						<td class="text-center">{{$item.mobile}}</td>
						<td class="text-center">{{$item.role_name}}</td>
						<td class="text-center">{{if $item.user_status}} 正常 {{else}} <span style="color:#F00;font-weight:700;">已禁用</span> {{/if}}</td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'Admin'|access:'forbid'}}
								{{if $item.user_status}}
								<a class="forbid" rel="{{$item.adminid}}" realname="{{$item.real_name}}" status="0" href="javascript:void(0)">禁用</a>
								{{else}}
								<a class="forbid" rel="{{$item.adminid}}" realname="{{$item.real_name}}" status="1" href="javascript:void(0)">解禁</a>
								{{/if}}
							{{/if}}

							{{if 'Admin'|access:'edit'}}
							<a class="custom-a" href="javascript:void(0);" onclick="edit({{$item.adminid}}, '{{$item.real_name}}');">编辑</a>
							{{/if}}

							{{if 'Admin'|access:'delete'}}
							<a class="del" rel="{{$item.adminid}}" href="javascript:void(0)">删除</a>
							{{/if}}
						</td>
					</tr>
    				{{/foreach}}
    			</tbody>
				<tfoot>
					<tr>
						<td colspan="16">
							<div class="pull-right page-block">
								<nav><ul class="pagination">{{$pageHtml nofilter}}</ul></nav>
							</div>
						</td>
					</tr>
				</tfoot>
            </table>
		</div>
	</div>
</div>

<script type="text/javascript">
	//表单提交
	$(function () {
		$('tbody tr td .del').click(function() {
			var id = $(this).attr('rel');
			layer.confirm('您确定要删除吗？', {
				title: "操作提示",
				btn: ['确定', '取消'] //按钮
			}, function() {
				$.ajax({
					type: 'post',
					data: {'admin_id': id},
					url: "{{'Admin/delete'|url}}",
					dataType: 'json',
					success: function (rsp) {
						if (rsp.code == 200) {
							success(rsp.msg, 1, '');
						} else {
							fail(rsp.msg, 3);
						}
					}
				});
			}, function(){
				return true;
			});
		});
		// 禁用/解禁。
		$('tbody tr td .forbid').click(function() {
			var id       = $(this).attr('rel');
			var realname = $(this).attr('realname');
			var status   = $(this).attr('status');
			var opLabel  = status ? '解禁' : '禁用';
			var title    = '您确定要' + opLabel + '该账号《' + realname + '》吗？';
			layer.confirm(title, {
				title: "操作提示",
				btn: ['确定', '取消'] //按钮
			}, function() {
				$.ajax({
					type: 'post',
					data: {'admin_id': id, 'status': status},
					url: "{{'Admin/forbid'|url}}",
					dataType: 'json',
					success: function (rsp) {
						if (rsp.code == 200) {
							success(rsp.msg, 1, '');
						} else {
							fail(rsp.msg, 3);
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
		var page_url = "{{'Admin/edit'|url}}?admin_id=" + id;
		postDialog('editAdminUser', page_url, title, 400, 300);
	}
	function add() {
		postDialog('addAdminUser', '{{'Admin'|url:'add'}}', '添加管理员', 400, 300);
	}
</script>

{{include file="common/footer.php"}}