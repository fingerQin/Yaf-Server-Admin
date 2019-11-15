{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>友情链接列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				{{if 'Link'|access:'add'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加友情链接</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Link', 'index');">帮助</button>
			</div>
		</div>
		
		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Link/index'|url}}" method="post">
					<div class="form-group">
						<input type="text" name="keywords" class="form-control" style="width:180px;" value="{{$keywords}}" placeholder="友情链接名称">
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
						<th class="text-center">ID</th>
						<th class="text-center">友情链接名称</th>
						<th class="text-center">分类名称</th>
						<th class="text-center">友情链接URL</th>
						<th class="text-center">友情链接图片</th>
						<th class="text-center">是否显示</th>
						<th class="text-center">修改时间</th>
						<th class="text-center">创建时间</th>
						<th class="text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.link_id}}</td>
						<td class="text-center">{{$item.link_name}}</td>
						<td class="text-center">{{$item.cat_name}}</td>
						<td class="text-center"><img width="60" src="{{$item.image_url}}" /></td>
						<td class="text-center">{{$item.cat_name}}</td>
						<td class="text-center">{{if $item.display}}显示{{else}}隐藏{{/if}}</td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'Link'|access:'add'}}
							<a href="###" onclick="edit({{$item.link_id}}, '{{$item.link_name}}')" title="修改">修改</a> | 
							{{/if}}

							{{if 'Link'|access:'delete'}}
							<a href="###" onclick="deleteDialog('deleteLink', '{{'Link'|url:delete:['link_id' => $item.link_id]}}', '{{$item.link_name}}')" title="删除">删除</a>
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
function add() {
	postDialog('addLink', '{{'Link/add'|url}}', '添加友情链接', 500, 420);
}
function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Link/edit'|url}}?link_id="+id;
	postDialog('editLink', page_url, title, 500, 420);
}
</script>

{{include file="common/footer.php"}}