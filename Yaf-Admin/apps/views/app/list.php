{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>APP 列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				{{if 'App'|access:'add'}}
				<a type="button" class="btn btn-mystyle btn-sm" href="javascript:add()">添加 APP</a>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('App', 'list');">帮助</button>
			</div>
		</div>
		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'app/list'|url}}" onsubmit="return submitBefore();" method="get">
					<div class="form-group">
						<span class="pull-left form-span">APP 类型:</span>
						<select name="appType" id="appType" class="form-control">
							<option value="-1" {{if $appType == -1}}selected{{/if}}>请选择</option>
							<option value="1" {{if $appType == 1}}selected{{/if}}>IOS</option>
							<option value="2" {{if $appType == 2}}selected{{/if}}>Android</option>
						</select>
					</div>
					<div class="form-group">
						<span class="pull-left form-span">Android 渠道:</span>
						<select name="channel" id="channel" class="form-control">
							<option value="" {{if $channel == ''}}selected{{/if}}>请选择</option>
							{{foreach $channelDict as $ch}}
							<option value="{{$ch}}" {{if $ch == $channel}}selected{{/if}}>{{$ch}}</option>
							{{/foreach}}
						</select>
					</div>
					<div class="form-group">
						<span class="pull-left form-span">&nbsp;APP 版本号</span>
						<input type="text" name="appV" id="appV" class="form-control" style="width: 180px;" value="{{$appV}}" placeholder="请输入版本号">
					</div>
					<div class="form-group">
						<button type="submit" class="form-control btn btn-info" ><span class="glyphicon glyphicon-search"></span> 查询</button>
					</div>
				</form>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="table-margin">
			<table class="table table-bordered table-header">
				<thead>
					<tr>
						<th class="w5 text-center">ID</th>
						<th class="w5 text-center">客户端类型</th>
						<th class="w15 text-center">升级标题</th>
						<th class="w5 text-center">APP 版本</th>
						<th class="w5 text-center">升级方式</th>
						<th class="w5 text-center">升级弹窗</th>
						<th class="w10 text-center">Android 渠道</th>
						<th class="w25 text-left">升级描述</th>
						<th class="w10 text-center">修改时间</th>
						<th class="w10 text-center">创建时间</th>
						<th class="w10 text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
				{{foreach $list as $item}}
    	            <tr>
						<td class="text-center">{{$item.id}}</td>
						<td class="text-center">{{$item.app_type_txt}}</td>
						<td class="text-center">{{$item.app_title}}</td>
						<td class="text-center">{{$item.app_v}}</td>
						<td class="text-center">{{$item.upgrade_way_txt}}</td>
						<td class="text-center">{{$item.dialog_repeat_txt}}</td>
						<td class="text-center">{{$item.channel}}</td>
						<td class="text-left">{{$item.app_desc}}</td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'App'|access:'edit'}}
							<a class="custom-a" href="javascript:edit({{$item.id}}, '{{$item.app_title}}')">编辑</a>
							{{/if}}
							{{if 'App'|access:'delete'}}
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
									<ul class="pagination">{{$page_html}}</ul>
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
					url: "{{'App/delete'|url}}",
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
		var page_url = "{{'App/edit'|url}}?id=" + id;
		postDialog('editApp', page_url, title, 550, 660);
	}
	function add() {
		postDialog('addApp', '{{'App'|url:'add'}}', '添加APP版本', 550, 660);
	}
</script>

{{include file="common/footer.php"}}