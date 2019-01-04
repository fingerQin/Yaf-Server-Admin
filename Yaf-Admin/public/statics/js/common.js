//滚动条
$(function() {
	$(":text").addClass('input-text');
})

/**
 * 全选checkbox,注意：标识checkbox id固定为为check_box
 * @param string name 列表check名称,如 uid[]
 */
function selectall(name) {
	if ($('#check_box').is(':checked')) {
		$("input[name='"+name+"']").each(function() {
  			$(this).prop("checked", true);
			
		});
	} else {
		$("input[name='"+name+"']").each(function() {
  			$(this).prop("checked", false );
		});
	}
}

/**
 * airDialog5版弹出框tips。
 * @param message  提示内容。
 * @param interval 间隔时间。单位秒。
 * @param icon     icon 图标：1-成功、5-失败。
 * @param jumpUrl  跳转 URL。为空字符串则刷新当前页。
 * @return void
 */
function dialogTips(message, interval, iconVal, jumpUrl) {
	layer.msg(message, {
		id : 'dialogTips' + '_' + Math.random(),
		icon: iconVal,
		time: interval * 1000
	}, function() {
		if (jumpUrl == 'parent') {
			parent.location.reload();
		} else if (jumpUrl == '') {
			window.location.reload();
		} else if (jumpUrl != undefined) {
			window.location.href=jumpUrl
		}
	});
}

/**
 * airDialog5版弹出框tips。
 * @param message  提示内容。
 * @param interval 间隔时间。单位秒。
 * @param jumpUrl  跳转 URL。
 * @return void
 */
function success(message, interval, jumpUrl)
{
	dialogTips(message, interval, 1, jumpUrl);
}

/**
 * airDialog5版弹出框tips。
 * @param message  提示内容。
 * @param interval 间隔时间。单位秒。
 * @param jumpUrl  跳转 URL。
 * @return void
 */
function fail(message, interval, jumpUrl)
{
	dialogTips(message, interval, 5, jumpUrl);
}

/**
 * 弹出一个添加/编辑操作的对话框。
 * @param  dialog_id  弹出框的ID。
 * @param  page_url   表单页面URL。
 * @param  title 	  弹出框名称。
 * @param  scrolling  ifream 是否滚动。yes、no。
 * @return void
 */
function postDialog(dialog_id, page_url, dialog_title, dialog_width, dialog_height, scrolling) {
	layer.open({
		id: dialog_id,
		type: 2,
		title: dialog_title,
		area: [dialog_width+'px', dialog_height+'px'],
		shade: [0.3, '#000'],
		shadeClose: false,
		scrollbar:scrolling,
		content: page_url
	});
}

/**
 * 弹出一个删除操作的对话框。
 * @param dialog_id 	弹出框的ID。
 * @param request_url 	执行删除操作的URL。
 * @param title 		要删除的记录的标题或名称。
 * @return void
 */
function deleteDialog(dialog_id, request_url, title) {
	layer.confirm('您确定要删除【' + title + '】吗？', {
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

/**
 * 弹出帮助文档弹框。
 * @param string ctrlName    控制器名称。
 * @param string actionName  操作名称。 
 */
function helpDialog(ctrlName, actionName)
{
	page_url = '/Index/index/gethelp?c=' + ctrlName + '&a=' + actionName;
	layer.open({
		id: 'helpDialogId',
		type: 2,
		title: '帮助文档',
		area: ['80%', '80%'],
		shade: [0.3, '#000'],
		shadeClose: true,
		scrollbar:true,
		content: page_url
	});
}