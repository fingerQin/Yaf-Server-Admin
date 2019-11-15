{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>短信模板列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
            </div>
            <div class="pull-right">
				{{if 'SmsTpl'|access:'add'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加模板</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('SmsTpl', 'lists');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
                <form action="{{'SmsTpl/lists'|url}}" method="get">
                    <div class="form-group">
						<input type="text" value="{{$send_key}}" class="form-control" style="width:150px;" name="send_key" placeholder="请输入模板KEY" />
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
						<th class="w10 text-left">模板 KEY</th>
                        <th class="w10 text-center">模板标题</th>
                        <th class="w10 text-center">触发类型</th>
                        <th class="w35 text-left">模板内容</th>
                        <th class="w10 text-center">创建时间</th>
                        <th class="w10 text-center">更新时间</th>
                        <th class="w10 text-center">操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.id}}</td>
						<td class="text-left">{{$item.send_key}}</td>
						<td class="text-center">{{$item.title}}</td>
                        <td class="text-center">{{$item.trigger_type_label}}</td>
                        <td class="text-left">{{$item.sms_body}}</td>
                        <td class="text-center">{{$item.c_time}}</td>
                        <td class="text-center">{{$item.u_time}}</td>
                        <td align="center">
                        {{if 'SmsTpl'|access:'edit'}}
                        <a href="###" onclick="edit({{$item.id}}, '{{$item.title}}')" title="修改">[修改]</a>
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
	postDialog('addTpl', '{{'SmsTpl/add'|url}}', '添加模板', 500, 350);
}
function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'SmsTpl/edit'|url}}?id="+id;
	postDialog('editTpl', page_url, title, 500, 350);
}
</script>

{{include file="common/footer.php"}}