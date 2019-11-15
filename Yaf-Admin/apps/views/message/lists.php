{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>系统消息列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
            </div>
            <div class="pull-right">
				{{if 'Message'|access:'send'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">发送系统消息</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Message', 'lists');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
                <form action="{{'Message/lists'|url}}" method="get">
                    <div class="form-group">
						<input type="text" value="{{$mobile}}" class="form-control" style="width:150px;" name="mobile" placeholder="请输入手机账号" />
                    </div>
                    <div class="form-group">
                        <select name="read_status" class="form-control" style="width:120px;">
                            <option value="-1">阅读状态</option>
                            <option {{if $read_status==0}}selected="selected"{{/if}} value="1">未读</option>
                            <option {{if $read_status==2}}selected="selected"{{/if}} value="2">已读</option>
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
						<th class="w5 text-center">类型</th>
                        <th class="w5 text-center">状态</th>
                        <th class="w10 text-center">手机号</th>
                        <th class="w15 text-left">标题</th>
                        <th class="w35 text-left">Url/内容</th>
                        <th class="w10 text-center">创建时间</th>
                        <th class="w10 text-center">更新时间</th>
                        <th class="w5 text-center">操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.msgid}}</td>
						<td class="text-center">{{$item.msg_type_label}}</td>
						<td class="text-center">{{$item.read_label}}</td>
                        <td class="text-center">{{$item.mobile}}</td>
                        <td class="text-left">{{$item.title}}</td>
                        <td class="text-left"><strong>{{$item.url}}</strong><br />{{$item.content}}</td>
                        <td class="text-center">{{$item.c_time}}</td>
                        <td class="text-center">{{$item.u_time}}</td>
                        <td align="center">
                        {{if 'Message'|access:'delete'}}
                        <a href="###" onclick="deleteDialog('deleteMessage', '{{'Message'|url:delete:['msgid' => $item.msgid]}}', '{{$item.title}}')" title="删除">删除</a>
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
	postDialog('addMessage', '{{'Message/send'|url}}', '发送系统消息', 500, 500);
}
</script>

{{include file="common/footer.php"}}