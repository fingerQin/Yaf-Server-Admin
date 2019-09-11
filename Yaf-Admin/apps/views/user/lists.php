{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>用户列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('User', 'index');">帮助</button>
			</div>
		</div>
		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'User/lists'|url}}" onsubmit="return submitBefore();" method="get">
					<div class="form-group">
						<span class="pull-left form-span">手机账号</span>
						<input type="text" name="mobile" id="mobile" class="form-control" style="width: 180px;" value="{{$mobile}}" placeholder="请输入用户手机账号">
					</div>
					<div class="form-group" style="width:415px;">
						<span class="pull-left form-span">下单时间</span>
						<input type="text" name="start_time" id="start_time" value="{{$start_time}}" size="20" class="date form-control" style="display:inline;width:160px;" /> ～ 
						<input type="text" name="end_time" id="end_time" value="{{$end_time}}" size="20" class="date form-control" style="display:inline;width:160px;" />
						<script type="text/javascript">
						Calendar.setup({
							weekNumbers: false,
							inputField : "start_time",
							trigger    : "start_time",
							dateFormat: "%Y-%m-%d %H:%I:%S",
							showTime: true,
							minuteStep: 1,
							onSelect   : function() {this.hide();}
						});

						Calendar.setup({
							weekNumbers: false,
							inputField : "end_time",
							trigger    : "end_time",
							dateFormat: "%Y-%m-%d %H:%I:%S",
							showTime: true,
							minuteStep: 1,
							onSelect   : function() {this.hide();}
						});
						</script>
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
						<th class="w5 text-center">用户 ID</th>
						<th class="w10 text-center">头像</th>
						<th class="w10 text-center">OpenID</th>
						<th class="w10 text-center">手机账号</th>
						<th class="w10 text-center">昵称</th>
						<th class="w5 text-center">注册平台</th>
						<th class="w5 text-center">应用市场</th>
						<th class="w10 text-center">最后登录IP</th>
						<th class="w10 text-center">最后登录时间</th>
						<th class="w10 text-center">注册时间</th>
						<th class="w5 text-center">用户状态</th>
						<th class="w10 text-center">操作</th>
					</tr>
				</thead>
				<tbody>
					{{foreach $list as $item}}
    				<tr>
						<td align="center">{{$item.userid}}</td>
						<td align="center"><img src="{{$item.headimg}}" alt="头像" width="120" /></td>
						<td align="center">{{$item.open_id}}</td>
						<td align="center">{{$item.mobile}}</td>
						<td align="center">{{$item.nickname}}</td>
						<td align="center">{{$item.platform}}</td>
						<td align="center">{{$item.app_market}}</td>
						<td align="center">{{$item.last_login_ip}}</td>
						<td align="center">{{$item.last_login_time}}</td>
						<td align="center">{{$item.c_time}}</td>
						<td align="center">{{$item.cur_status}}</td>
                        <td align="center">
							{{if 'User'|access:'editPwd'}}
                            <a href="###" onclick="editPwd({{$item.userid}}, '{{$item.mobile}}')">[改密码]</a><br />
							{{/if}}

							{{if 'User'|access:'status'}}
							<a href="###" onclick="status({{$item.userid}}, '{{$item.mobile}}')">[改状态]</a><br />
							{{/if}}

							{{if 'User'|access:'clearAccountLoginLock'}}
                            <a href="###" onclick="clearAccountLoginLock({{$item.userid}}, '{{$item.mobile}}')">[清除登录锁定]</a><br />
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
									<ul class="pagination">{{$pageHtml nofilter}}</ul>
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
function editPwd(id, name) {
	var title = '修改『' + name + '』的密码';
	var page_url = "{{'User/editPwd'|url}}?userid="+id;
	postDialog('editUser', page_url, title, 300, 200);
}
function status(id, name) {
	var title = '状态设置『' + name + '』';
	var page_url = "{{'User/status'|url}}?userid="+id;
	postDialog('editStatus', page_url, title, 300, 200);
}

// 清除用户登录锁定。
function clearAccountLoginLock(userid, mobile) {
    var pageUrl = "{{'User/clearAccountLoginLock'|url}}?userid="+userid;
    layer.confirm('您确定要清除【' + mobile + '】的登录锁定吗？', {
		btn: ['确定', '取消'],
		title: '操作提示'
	}, 
	function() {
		$.ajax({
			type: "GET",
			url: pageUrl,
			dataType: 'json',
			success: function(data){
				if (data.code == 200) {
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