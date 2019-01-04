{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>配置列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				{{if 'Config'|access:'add'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加配置</button>
				{{/if}}

				{{if 'Config'|access:'ClearCache'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="clearCache();">清除配置缓存</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('config', 'index');">帮助</button>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="table-margin">
			<table class="table table-bordered table-header" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th class="w5 text-center">ID</th>
						<th class="w15 text-center">配置标题</th>
						<th class="w15 text-center">配置编码</th>
						<th class="w15 text-center">配置值</th>
						<th class="w20 text-center">描述</th>
						<th class="w10 text-center">修改时间</th>
						<th class="w10 text-center">创建时间</th>
						<th class="w10 text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
				{{foreach $list as $item}}
					<tr>
						<td class="text-center">{{$item.configid}}</td>
						<td class="text-center">{{$item.title}}</td>
						<td class="text-center">{{$item.cfg_key}}</td>
						<td class="text-center">{{$item.cfg_value}}</td>
						<td class="text-center">{{$item.description}}</td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'Config'|access:'edit'}}
							<a href="###" onclick="edit({{$item.configid}}, '{{$item.title}}')" title="修改">修改</a> | 
							{{/if}}
							{{if 'Config'|access:'delete'}}
							<a href="###" onclick="deleteDialog('deleteType', '{{'Config'|url:'delete':['config_id' => $item.configid]}}', '{{$item.title}}')" title="删除">删除</a>
							{{/if}}
						</td>
					</tr>
				{{/foreach}}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="16">
							<div class="pull-right page-block">
								<nav><ul class="pagination">{{$page_html}}</ul></nav>
							</div>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Config/edit'|url}}?config_id=" + id;
	postDialog('editType', page_url, title, 380, 400);
}
function add() {
	postDialog('addType', '{{'Config'|url:'add'}}', '添加配置', 380, 400);
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
			url: '{{'Config'|url:'clearCache'}}',
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

{{include file="common/footer.php"}}