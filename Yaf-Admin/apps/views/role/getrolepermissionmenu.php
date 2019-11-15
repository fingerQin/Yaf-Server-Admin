{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>设置角色权限</h4>
			</div>
			<div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="window.history.back();">返回</button>
			</div>
		</div>
		<div class="clearfix"></div>
			<div class="main left quanxian">
				<form id="fromID">
					<input type="hidden" name="roleid" value="{{$roleid}}"/>
					{{foreach $menus as $menu}}
						<div class="con-qx">
							<h1 class="a">
								<label>
									<input style="margin-right:10px;"
									{{foreach $priv_menus as $ii}}
									{{if $ii == $menu.menuid}}checked="checked"{{/if}} 
									{{/foreach}} 
									id="{{$menu.menuid}}" parentid="{{$menu.parentid}}" name="menuid[]" type="checkbox" size="3" value="{{$menu.menuid}}">{{$menu.menu_name}}
								</label>
							</h1>
								
							{{foreach $menu.sub as $sub_m}}
							<div class="b">
								<div class="bb">
									<label>
										<input 
										{{foreach $priv_menus as $ii}}
										{{if $ii == $sub_m.menuid}}checked="checked"{{/if}} 
										{{/foreach}} 
										id="{{$sub_m.menuid}}" parentid="{{$sub_m.parentid}}" name="menuid[]" type="checkbox" size="3" value="{{$sub_m.menuid}}">&nbsp; {{$sub_m.menu_name}}
									</label>
								</div>
								<div class="c">
								{{foreach $sub_m.sub as $ss_m}}
									<label class="cc">
										<input 
										{{foreach $priv_menus as $ii}}
										{{if $ii == $ss_m.menuid}}checked="checked"{{/if}} 
										{{/foreach}} 
										id="{{$ss_m.menuid}}" parentid="{{$ss_m.parentid}}" name="menuid[]" type="checkbox" size="3" value="{{$ss_m.menuid}}">&nbsp;{{$ss_m.menu_name}}
									</label>
									{{foreach $ss_m.sub as $sssub}}
									<label class="cc">
										<input 
										{{foreach $priv_menus as $ii}}
										{{if $ii == $sssub.menuid}}checked="checked"{{/if}} 
										{{/foreach}} 
										id="{{$sssub.menuid}}" parentid="{{$sssub.parentid}}" name="menuid[]" type="checkbox" size="3" value="{{$sssub.menuid}}">&nbsp;{{$sssub.menu_name}}
									</label>
									{{/foreach}}
								{{/foreach}}
								</div>
							</div>
							
							{{/foreach}}
							</div>
						{{/foreach}}

					<tr>
						<td></td>
						<td>
							<span><input class="btn btn-default" id="submitID" type="button" value="保存并提交"></span>
							<span><input class="btn btn-default" onclick="window.history.back();" type="button" value="返回"></span>
						</td>
					</tr>
				</div>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
<!--

$(document).ready(function(){
	$('#submitID').click(function () {
		var submitBtn = $(this);
		submitBtn.val('提交中...');
		var fromData = $('#fromID').serialize();
		$.ajax({
			type: 'post',
			data:fromData,
			dataType:'json',
			url: "{{'Role/setPermission'|url}}",
			success: function(rsp) {
				submitBtn.val('保存并提交');
				if (rsp.code == 200) {
					success(rsp.msg, 1);
				} else {
					fail(rsp.msg, 3);
				}
			}
		});
	});

	$(":checkbox").click(function(){
		if (this.checked) {
			var menu_id = this.value; // 获取被点击的菜单ID。
			var obj = $('#' + menu_id); // 获取菜单checkbox对象。
			var node_parentid = obj.attr('parentid'); // 获取菜单的父ID。
			$('#' + node_parentid).prop('checked', true); // 将菜单父ID选中。
			$('input[parentid="' + menu_id + '"]').prop('checked', true); // 将当前被选中的菜单的子菜单选中。
			
		    $('input[parentid="' + menu_id + '"]').each(function(index, data){
		    	$('input[parentid="' + data.value + '"]').prop('checked', true); // 将当前被选中的菜单的子菜单的子菜单选中。
			});

			var obj = $('#' + node_parentid); // 获取菜单checkbox对象。
			var node_parentid_fu = obj.attr('parentid'); // 获取菜单的父ID。
			var checked_count_fu = $('input[parentid="' + node_parentid_fu + '"]:checked').length; // 获取当前被取消的菜单的同一个父ID的菜单还留存多少个选中的。
			if (checked_count_fu > 0) {
				$('#' + node_parentid_fu).prop('checked', true); // 如果同父ID的菜单已经没有了。则将父菜单取消。
			}
		} else {
			var menu_id = this.value; // 获取被点击的菜单ID。
			var obj = $('#' + menu_id); // 获取菜单checkbox对象。
			var node_parentid = obj.attr('parentid'); // 获取菜单的父ID。
			var checked_count = $('input[parentid="' + node_parentid + '"]:checked').length; // 获取当前被取消的菜单的同一个父ID的菜单还留存多少个选中的。
			if (checked_count == 0) {
				$('#' + node_parentid).prop('checked', false); // 如果同父ID的菜单已经没有了。则将父菜单取消。
			}

			$('input[parentid="' + menu_id + '"]').prop('checked', false); // 将当前被取消的菜单的子菜单取消。
			$('input[parentid="' + menu_id + '"]').each(function(index, data){
		    	$('input[parentid="' + data.value + '"]').prop('checked', false); // 将当前被取消的菜单的子菜单的子菜单取消。
				var checked_count = $('input[parentid="' + node_parentid + '"]:checked').length
			});

			var obj = $('#' + node_parentid); // 获取菜单checkbox对象。
			var node_parentid_fu = obj.attr('parentid'); // 获取菜单的父ID。
			var checked_count_fu = $('input[parentid="' + node_parentid_fu + '"]:checked').length; // 获取当前被取消的菜单的同一个父ID的菜单还留存多少个选中的。
			if (checked_count_fu == 0) {
				$('#' + node_parentid_fu).prop('checked', false); // 如果同父ID的菜单已经没有了。则将父菜单取消。
			}
		}
	});
});

//-->
</script>

{{include file="common/footer.php"}}