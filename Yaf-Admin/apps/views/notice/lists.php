{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>公告列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
            </div>
            <div class="pull-right">
				{{if 'Notice'|access:'add'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加公告</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Notice', 'lists');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
                <form action="{{'Notice/lists'|url}}" method="get">
                    <div class="form-group">
						<input type="text" value="{{$title}}" class="form-control" style="width:180px;" name="title" placeholder="请输入标题关键词查询" />
					</div>
					<div class="form-group">
						<select name="status" class="form-control">
							<option value="-1">状态</option>
							<option {{if $status==1}}selected="selected"{{/if}} value="1">显示</option>
                            <option {{if $status==0}}selected="selected"{{/if}} value="2">隐藏</option>
						</select>
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
						<th class="w10 text-center">状态</th>
                        <th class="w15 text-left">公告标题</th>
                        <th class="w30 text-left">公告摘要</th>
                        <th class="w10 text-center">所属终端</th>
                        <th class="w10 text-center">创建时间</th>
                        <th class="w10 text-center">更新时间</th>
                        <th class="w10 text-center">操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
                        <td class="text-center">{{$item.noticeid}}</td>
                        <td class="text-center">{{$item.status_label}}</td>
						<td class="text-left">{{$item.title}}</td>
						<td class="text-left">{{$item.summary}}</td>
						<td class="text-center">{{$item.terminal_label}}</td>
                        <td class="text-center">{{$item.c_time}}</td>
                        <td class="text-center">{{$item.u_time}}</td>
                        <td align="center">
                        {{if 'Notice'|access:'edit'}}
                        <a href="###" onclick="edit({{$item.noticeid}}, '{{$item.title}}')" title="修改">[修改]</a>
                        {{/if}}
                        {{if 'Notice'|access:'status'}}
                        {{if $item.cur_status==1}}
                        <a href="###" onclick="statusDialog('statusNotice', 0, '{{'Notice'|url:status:['noticeid' => $item.noticeid, 'status' => 0]}}', '{{$item.title}}')" title="隐藏">[隐藏]</a>
                        {{else}}
                        <a href="###" onclick="statusDialog('statusNotice', 1, '{{'Notice'|url:status:['noticeid' => $item.noticeid, 'status' => 1]}}', '{{$item.title}}')" title="显示">[显示]</a>
                        {{/if}}
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
	postDialog('addNotice', '{{'Notice/add'|url}}', '添加公告', 750, 750);
}
function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Notice/edit'|url}}?noticeid="+id;
	postDialog('editNotice', page_url, title, 750, 750);
}
/**
 * 弹出一个更新操作的对话框。
 * @param status        状态。
 * @param dialog_id 	弹出框的ID。
 * @param request_url 	执行删除操作的URL。
 * @param title 		要删除的记录的标题或名称。
 * @return void
 */
function statusDialog(status, dialog_id, request_url, title) {
    var msg = '您确定要隐藏【' + title + '】吗？';
    if (status == 1) {
        msg = '您确定要显示【' + title + '】吗？';
    }
	layer.confirm(msg, {
		btn: ['确定', '取消'],
		title: '操作提示'
	}, 
	function() {
		$.ajax({
			type: "GET",
			url: request_url,
			dataType: 'json',
			success: function(data){
				if (data.code == 200) {
					location.reload();
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