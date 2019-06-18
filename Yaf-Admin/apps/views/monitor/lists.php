{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>告警列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
            </div>
            <div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Monitor', 'lists');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
                <form action="{{'Monitor/lists'|url}}" method="get">
                    <div class="form-group">
                        <select name="read_status" class="form-control" style="width:180px;">
                            <option value="">告警编码</option>
                            {{foreach $codeDict as $mcode => $name}}
                            <option {{if $mcode==$code}}selected="selected"{{/if}} value="{{$mcode}}">{{$name}}</option>
                            {{/foreach}}
                        </select>
                    </div>

                    <div class="form-group" style="width:415px">
						<span class="pull-left form-span">告警时间:</span>
						<input type="text" name="start_time" id="start_time" value="{{$start_time}}" size="20" class="date form-control" style="display:inline;width:158px;" readonly="" /> ～ 
						<input type="text" name="end_time" id="end_time" value="{{$end_time}}" size="20" class="date form-control" style="display:inline;width:158px;" readonly="" />
						<script type="text/javascript">
							Calendar.setup({
								weekNumbers: false,
								inputField : "start_time",
								trigger    : "start_time",
								dateFormat : "%Y-%m-%d %H:%I:%S",
								showTime   : true,
								minuteStep : 1,
								onSelect   : function() {this.hide();}
							});
							Calendar.setup({
								weekNumbers: false,
								inputField : "end_time",
								trigger    : "end_time",
								dateFormat : "%Y-%m-%d %H:%I:%S",
								showTime   : true,
								minuteStep : 1,
								onSelect   : function() {this.hide();}
							});
						</script>
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
						<th class="w5 text-center">流水号</th>
                        <th class="w5 text-center">CODE 编码</th>
                        <th class="w10 text-center">备注</th>
                        <th class="w10 text-center">创建时间</th>
                        <th class="w5 text-center">操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $result.list as $item}}
    				<tr>
						<td class="text-center">{{$item.id}}</td>
						<td class="text-center">{{$item.serial_no}}</td>
						<td class="text-center">{{$item.code_label}}</td>
                        <td class="text-center">{{$item.remark}}</td>
                        <td class="text-center">{{$item.c_time}}</td>
                        <td align="center">
                        {{if 'Monitor'|access:'detail'}}
                        <a href="###" onclick="view('{{$item.id}}')" title="查看告警">查看告警</a>
                        {{/if}}
                        {{if $item.status !=1 && 'Monitor'|access:'detail'}}
                        <a href="###" onclick="processed('{{$item.id}}')" title="处理告警">处理告警</a>
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
// 处理告警。
function processed(id) {
    postDialog('monitorProcessed', '{{'Monitor'|url:'processed'}}?id=' + id, '处理告警', 400, 300);
}
// 查看告警详情。
function view(id) {
    postDialog('monitorDetail', '{{'Monitor'|url:'detail'}}?id=' + id, '查看告警', 550, 500);
}
</script>

{{include file="common/footer.php"}}