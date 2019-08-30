{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>广告位列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				{{if 'Ad'|access:'addPosition'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加广告位</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Ad', 'position');">帮助</button>
			</div>
		</div>
		<div class="clearfix"></div>

        <div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Ad/position'|url}}" onsubmit="return submitBefore();" method="get">
					<div class="form-group">
						<input type="text" name="appV" id="appV" class="form-control" style="width: 180px;" value="{{$keywords}}" placeholder="广告位置名称">
					</div>
					<div class="form-group">
						<button type="submit" class="form-control btn btn-info" ><span class="glyphicon glyphicon-search"></span> 查询</button>
					</div>
				</form>
			</div>
		</div>

	    <div class="table-margin">
			<table class="table table-bordered table-header" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th class="w10 text-center">ID</th>
						<th class="w15 text-center">广告位置名称</th>
						<th class="w10 text-center">广告编码</th>
						<th class="w5 text-center">可展示广告数量</th>
						<th class="w15 text-center">修改时间</th>
						<th class="w15 text-center">创建时间</th>
						<th class="w15 text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
                	{{foreach $list as $item}}
    	            <tr>
                        <td align="center">{{$item.pos_id}}</td>
                        <td align="center">{{$item.pos_name}}</td>
                        <td align="center">{{$item.pos_code}}</td>
                        <td align="center">{{$item.pos_ad_count}}</td>
                        <td align="center">{{$item.u_time}}</td>
                        <td align="center">{{$item.c_time}}</td>
                        <td align="center">
							{{if 'Ad'|access:'editPosition'}}
                            <a href="###" onclick="edit({{$item.pos_id}}, '{{$item.pos_name}}')">修改</a> | 
							{{/if}}
							{{if 'Ad'|access:'deletePosition'}}
                            <a href="###" onclick="deleteDialog('positionDelete', '{{'Ad'|url:'deletePosition':['pos_id' => $item.pos_id]}}', '{{$item.pos_name}}')">删除</a> | 
							{{/if}}
							{{if 'Ad'|access:'adlist'}}
                            <a href="###" onclick="setAdPosValue({{$item.pos_id}}, '{{$item.pos_name}}')">广告位管理</a>
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
function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Ad/editPosition'|url}}?pos_id="+id;
	postDialog('positionEdit', page_url, title, 400, 250);
}

function add() {
	postDialog('addPosition', '{{'Ad/addPosition'|url}}', '添加广告位', 400, 250);
}

function setAdPosValue(id, name) {
	page_url = '{{'Ad/adlist'|url}}?pos_id='+id;
	title = '管理 『 '+name+' 』位置广告';
	postDialog(id, page_url, title, 1000, 680, true);
}
</script>

{{include file="common/footer.php"}}