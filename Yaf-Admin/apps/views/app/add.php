{{include file="common/header.php"}}

<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">客户端类型：</th>
				<td>
					<select name="app_type" class="form-control">
						<option value="1">IOS</option>
						<option value="2">Android</option>
					</selected>
				</td>
			</tr>
			<tr>
				<th class="left-txt">版本号：</th>
				<td>
					<input type="text" name="app_v" id="app_v" size="20" class="input-text" value="">
					<div class="w90" style="color:grey;"> （当前 APP 版本号。eg:1.0.0）</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">Android 渠道：</th>
				<td>
					<select name="channel" id="channel" class="form-control">
						<option value="">请选择</option>
						{{foreach $channelDict as $ch => $chName}}
						<option value="{{$ch}}">{{$chName}}</option>
						{{/foreach}}
					</select>
					<div class="w90" style="color:grey;">（Android 才设置，iOS 不用选择）</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">下载地址：</th>
				<td>
					<input type="text" name="url" id="url" size="20" class="input-text" value="">
					<div class="w90" style="color:grey;"> （当前 APP 版本的下载 URL，iOS 请填写 AppSstore 下载地址）</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">升级方式：</th>
				<td>
					<select name="upgrade_way" class="form-control">
						<option value="0">不升级</option>
						<option value="1">建议升级</option>
						<option value="2">强制升级</option>
						<option value="3">应用关闭</option>
					</select>
					<div class="w90" style="color:grey;">（当前 APP 升级到新版时的升级方式）</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">升级弹窗：</th>
				<td>
					<select class="form-control" name="dialog_repeat">
						<option value="0">只弹一次</option>
						<option value="1">每次都弹</option>
					</select>
					<div class="w90" style="color:grey;">（仅当升级方式为“建议升级”时有效）</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">升级标题：</th>
				<td>
					<input type="text" name="app_title" id="app_title" size="20" class="input-text" value=""> 
					<div class="w90" style="color:grey;">（低于此版本的 APP 升级到此版时,显示在旧 APP 当中的升级标题）</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">升级描述：</th>
				<td>
					<textarea name="app_desc" id="app_desc" class="textarea"></textarea>
					<div class="w90" style="color:grey;">（当前版本针对旧版所做的功能的描述。旧版升级到该版本时会在升级框中显示）</div>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
				</td>
			</tr>
		</table>
	</form>
</div>

<script type="text/javascript">
	//表单提交
	$(function (){
		$('#submitID').click(function () {
			var fromData = $('#fromID').serialize();
			$.ajax({
				type: 'post',
				data:fromData,
				dataType:'json',
				url: "{{'app/add'|url}}",
				success: function(rsp) {
					if(rsp.code == 200) {
						success(rsp.msg, 1, 'parent');
					}else{
						fail(rsp.msg, 3);
					}
				}
			});
		});
	});
</script>

{{include file="common/header.php"}}