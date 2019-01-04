{{include file="common/header.php"}}


<div class="main">
	<form id="fromID">
		<table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">手机号码：</th>
				<td><input type="text" name="mobilephone" class="input-text" value="{{$detail.mobile}}"></td>
			</tr>
			<tr>
				<th class="left-txt">密码：</th>
				<td>
					<input type="password" name="password" class="input-text" value="">
					<div class="w90" style="color:grey;">（不填表示不修改）</div>
				</td>
			</tr>
			<tr>
				<th class="left-txt">真实姓名：</th>
				<td>
					<input type="text" name="realname" class="input-text" value="{{$detail.real_name}}">
				</td>
			</tr>
			<tr>
				<th class="left-txt">角色：</th>
				<td>
					<select name="roleid" class="form-control">
						<option value="-1">选择角色</option>
						{{foreach $roles as $role}}
						<option value="{{$role.roleid}}" {{if $detail.roleid == $role.roleid}}selected="selected"{{/if}}>{{$role.role_name}}</option>
						{{/foreach}}
					</select>
				</td>
			</tr>

			<tr>
				<td></td>
				<td>
					<input name="admin_id" type="hidden" value="{{$detail.adminid}}" />
					<span><input class="btn btn-default" id="submitID" type="button" value="保存"></span>
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
				data: fromData,
				dataType: 'json',
				url: "{{'Admin/edit'|url}}",
				success: function(rsp) {
					if (rsp.code == 200) {
						success(rsp.msg, 1, 'parent');
                    } else {
						fail(rsp.msg, 3);
                    }
				}
			});
		});
	});
</script>

{{include file="common/footer.php"}}