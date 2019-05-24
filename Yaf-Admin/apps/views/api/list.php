{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>API 应用列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				{{if 'Api'|access:'add'}}
				<a type="button" class="btn btn-mystyle btn-sm" href="javascript:add();">添加 API 应用</a>
				{{/if}}

				{{if 'Api'|access:'clearCache'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="clearCache();">清除配置缓存</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Api', 'list');">帮助</button>
			</div>
		</div>

		<div class="table-margin">
			<table class="table table-bordered table-header">
				<thead>
					<tr>
						<th class="w5 text-center">API ID</th>
						<th class="w15 text-center">应用类型</th>
						<th class="w15 text-left">应用名称</th>
						<th class="w15 text-left">应用英文名称</th>
						<th class="w15 text-center">应用密钥</th>
						<th class="w15 text-center">修改时间</th>
						<th class="w15 text-center">创建时间</th>
						<th class="w15 text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
					{{foreach $list as $item}}
    	            <tr>
						<td class="text-center">{{$item.id}}</td>
						<td class="text-center">{{$item.api_type}}</td>
						<td class="text-left">{{$item.api_name}}</td>
						<td class="text-left">{{$item.api_key}}</td>
						<td class="text-center">{{$item.api_secret}}</td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'Api'|access:'edit'}}
							<a class="custom-a" href="javascript:edit({{$item.id}}, '{{$item.api_name}}');">编辑</a>
							{{/if}}
							{{if 'Api'|access:'delete'}}
							<a class="del" rel="{{$item.id}}" href="javascript:void(0)">删除</a>
							{{/if}}
						</td>
					</tr>
					{{/foreach}}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="16">
							<div class="pull-right page-block">
								<nav>
									<ul class="pagination">
										{{$page_html}}
									</ul>
								</nav>
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
		$('tbody tr td .del').click(function () {
			var id = $(this).attr('rel');
			layer.confirm('您确定要删除吗？', {
				title: "操作提示",
				btn: ['确定', '取消'] //按钮
			}, function() {
				$.ajax({
					type: 'post',
					data: {'id': id},
					url: "{{'Api/delete'|url}}",
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
		var page_url = "{{'Api/edit'|url}}?id=" + id;
		postDialog('editApi', page_url, title, 600, 590);
	}
	function add() {
		postDialog('addApi', '{{'Api'|url:'add'}}', '添加 API 密钥', 600, 590);
	}

	/**
	* 清除配置缓存。
	*/
	function clearCache() {
		layer.confirm('您确定要清除配置缓存吗？', {
			btn: ['确定', '取消'],
			title: '操作提示'
		}, 
		function() {
			$.ajax({
				type: "GET",
				url: '{{'Api'|url:'clearCache'}}',
				dataType: 'json',
				success: function(data){
					if (data.code == 200) {
						dialogTips(data.msg, 3);
					} else {
						dialogTips(data.msg, 5);
						return false;
					}
				}
			});
		},
		function(){
			// 点击取消按钮啥事也不做。
		});
	}
</script>

{{include file="common/header.php"}}