{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>短信发送日志列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
                <form action="{{'Sms/lists'|url}}" method="get">
                    <div class="form-group">
						<input type="text" value="{{$mobile}}" class="form-control" style="width:150px;" name="mobile" placeholder="请输入手机号" />
					</div>
					<div class="form-group" style="width:415px">
						<span class="pull-left form-span">发送时间:</span>
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
						<select name="status" class="form-control">
							<option value="-1">状态</option>
							<option {{if $status==1}}selected="selected"{{/if}} value="1">待发送</option>
                            <option {{if $status==2}}selected="selected"{{/if}} value="2">成功</option>
                            <option {{if $status==3}}selected="selected"{{/if}} value="3">失败</option>
						</select>
                    </div>
                    <div class="form-group">
						<select name="tpl_id" class="form-control">
                            <option value="-1">模板</option>
                            {{foreach $tpls as $tpl}}
                            <option {{if $tpl_id==$tpl.id}}selected="selected"{{/if}} value="{{$tpl.id}}">{{$tpl.title}}</option>
                            {{/foreach}}
						</select>
                    </div>
                    <div class="form-group">
						<select name="channel_id" class="form-control">
                            <option value="-1">通道</option>
                            {{foreach $channels as $channel}}
                            <option {{if $channel_id==$channel.id}}selected="selected"{{/if}} value="{{$channel.id}}">{{$channel.title}}</option>
                            {{/foreach}}
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
						<th class="w5 text-center">手机号</th>
						<th class="w5 text-center">状态</th>
						<th class="w10 text-center">ErrorMsg</th>
                        <th class="w10 text-center">模板</th>
                        <th class="w5 text-center">通道</th>
                        <th class="w5 text-center">验证码</th>
						<th class="w25 text-left">内容</th>
                        <th class="w5 text-center">IP</th>
                        <th class="w5 text-center">平台</th>
						<th class="w10 text-center">时间</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.id}}</td>
						<td class="text-center">{{$item.mobile}}</td>
						<td class="text-center">{{$item.sms_status}}</td>
						<td class="text-center">{{$item.error_msg}}</td>
						<td class="text-center">{{$item.tpl_name}}</td>
						<td class="text-center">{{$item.channel_name}}</td>
						<td class="text-center">{{$item.verify_code}}</td>
                        <td class="text-left">{{$item.content}}</td>
                        <td class="text-center">{{$item.ip}}</td>
                        <td class="text-center">{{$item.platform}}</td>
                        <td class="text-center">{{$item.c_time}}</td>
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

{{include file="common/footer.php"}}