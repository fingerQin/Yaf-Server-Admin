{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>黑名单列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
            </div>
            <div class="pull-right">
				{{if 'Sms'|access:'addBlist'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加配置</button>
				{{/if}}

				{{if 'Sms'|access:'clearBlacklistCache'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="clearCache();">清除配置缓存</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Sms', 'blacklist');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
                <form action="{{'Sms/blacklist'|url}}" method="get">
                    <div class="form-group">
						<input type="text" value="{{$mobile}}" class="form-control" style="width:150px;" name="mobile" placeholder="请输入手机号" />
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
						<th class="w5 text-center">ID</th>
						<th class="w10 text-center">手机号</th>
                        <th class="w10 text-center">创建时间</th>
                        <th class="w10 text-center">操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.id}}</td>
						<td class="text-center">{{$item.mobile}}</td>
                        <td class="text-center">{{$item.c_time}}</td>
                        <td class="text-center">
                            {{if 'Sms'|access:'deleteBlist'}}
							<a href="###" onclick="deleteDialog('deleteBlist', '{{'Sms'|url:'deleteBlist':['id' => $item.id]}}', '{{$item.mobile}}')" title="删除">删除</a>
							{{/if}}
                        </td>
					</tr>
    				{{/foreach}}
    			</tbody>
				<tfoot>
					<tr>
						<td colspan="16">
							<div class="pull-right page-block">
								<nav><ul class="pagination">{{$pageHtml}}</ul></nav>
							</div>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">

function add() {
	postDialog('addBlist', '{{'Sms'|url:'addblist'}}', '添加黑名单', 380, 400);
}

/**
 * 清除配置缓存。
 */
function clearCache() {
	layer.confirm('您确定要清除黑名单缓存吗？', {
		btn: ['确定', '取消'],
		title: '操作提示'
	}, 
	function() {
		$.ajax({
			type: "GET",
			url: '{{'Sms'|url:'clearBlacklistCache'}}',
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