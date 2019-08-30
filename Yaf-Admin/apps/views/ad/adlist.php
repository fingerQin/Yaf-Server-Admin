{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>广告列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
                {{if 'Ad'|access:'addAd'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加广告</button>
                {{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Ad', 'adlist');">帮助</button>
			</div>
		</div>
		<div class="clearfix"></div>

        <div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Ad/adlist'|url}}" onsubmit="return submitBefore();" method="get">
					<div class="form-group">
						<input type="text" name="ad_name" id="ad_name" class="form-control" style="width: 180px;" value="{{$ad_name}}" placeholder="广告名称">
                    </div>
                    <div class="form-group">
                        <span class="pull-left form-span">显示状态</span>
                        <select id="display" name="display" class="form-control" style="width:100px;">
                            <option value="-1">全部</option>
                            <option {{if $display==0}}selected="selected"{{/if}} value="0">隐藏</option>
                            <option {{if $display==1}}selected="selected"{{/if}} value="1">显示</option>
						</select>
					</div>
					<div class="form-group">
                        <input type="hidden" name="pos_id" value="{{$pos_id}}" />
						<button type="submit" class="form-control btn btn-info" ><span class="glyphicon glyphicon-search"></span> 查询</button>
					</div>
				</form>
			</div>
		</div>

	    <div class="table-margin">
            <form method="get" action="{{'Ad/adSort'|url}}">
                <table class="table table-bordered table-header" width="100%" cellspacing="0" style="margin-bottom:0px;">
                    <thead>
                        <tr>
                            <th class="w5 text-center">排序</th>
                            <th class="w5 text-center">ID</th>
                            <th class="w15 text-center">广告图片</th>
                            <th class="w20 text-center">广告名称</th>
                            <th class="w5 text-center">终端</th>
                            <th class="w15 text-center">生效(失败)时间</th>
                            <th class="w5 text-center">状态</th>
                            <th class="w5 text-center">备注</th>
                            <th class="w10 text-center">创建时间</th>
                            <th class="w10 text-center">管理操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{foreach $list as $item}}
                        <tr>
                            <td align='center'><input name='listorders[{{$item.ad_id}}]' type='text' size='3' value='{{$item.listorder}}' class='input-text' style="text-align:center;"></td>
                            <td align="center">{{$item.ad_id}}</td>
                            <td align="center"><a target="_blank" href="{{$item.ad_url}}"><img style="width:120px" src="{{$item.ad_image_url}}" /></a></td>
                            <td align="center">{{$item.ad_name}}</td>
                            <td align="center">{{$item.terminal_label nofilter}}</td>
                            <td align="center">开始:{{$item.start_time}}<br/><p>结束:{{$item.end_time}}</p></td>
                            <td align="center">{{if $item.display}}显示{{else}}隐藏{{/if}}</td>
                            <td align="center">
                                <a id="view_remark_{{$item.ad_id}}" title="{{$item.remark}}" href="###" onClick="viewRemark('view_remark_{{$item.ad_id}}')">查看</a>
                            </td>
                            <td align="center">{{$item.c_time}}</td>
                            <td align="center">
                                {{if 'Ad'|access:'editAd'}}
                                <a href="###" onclick="edit({{$item.ad_id}}, '{{$item.ad_name}}')" title="修改">修改</a> | 
                                {{/if}}
                                {{if 'Ad'|access:'deleteAd'}}
                                <a href="###" onclick="deleteDialog('adDelete', '{{'Ad'|url:'deleteAd':['ad_id' => $item.ad_id]}}', '{{$item.ad_name}}')">删除</a>
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
                {{if 'Ad'|access:'adSort'}}
                <div class="btn" style="padding-left:0px;">
                    <input type="hidden" name="pos_id" value="{{$pos_id}}" />
                    <input type="button" id="form_submit" class="button" name="dosubmit" value="排序" />
                </div>
                {{/if}}
            </form>
		</div>
	</div>
</div>

<script type="text/javascript">

$(document).ready(function(){
	$('#form_submit').click(function(){
	    $.ajax({
	    	type: 'post',
            url: $('form').eq(1).attr('action'),
            dataType: 'json',
            data: $('form').eq(1).serialize(),
            success: function(data) {
                if (data.code == 200) {
                	window.location.reload();
                } else {
                	dialogTips(data.msg, 3);
                }
            }
	    });
	});
});

function add() {
	postDialog('addAd', '{{'Ad'|url:'addAd':['pos_id' => $pos_id]}}', '添加广告', 500, 540);
}

function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Ad/editAd'|url}}?ad_id="+id;
	postDialog('adEdit', page_url, title, 500, 540);
}

function viewRemark(id) {
    layer.open({
        id : 'view_remark' + '_' + Math.random(),
        title: '查看备注',
        content: $('#'+id).attr('title')
    });
}

</script>

{{include file="common/footer.php"}}