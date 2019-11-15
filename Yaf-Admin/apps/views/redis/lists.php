{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>Redis KEY 列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span></h4>
            </div>
            <div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Redis', 'lists');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
                <div class="form-group">
                    <input type="text" value="" class="form-control" style="width:300px;" name="redis_key" id="redis_key" placeholder="请输入待删除的 redis key" />
                </div>
                <div class="form-group">
                    <button type="button" onclick="deleteRedis()" class="form-control btn btn-info"><span class="glyphicon"></span> 删除</button>
                </div>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="table-margin">
			<table class="table table-bordered table-header">
				<thead>
					<tr>
						<th class="w10 text-left">KEY</th>
                        <th class="text-left">KEY 名称</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $redisKeys as $key => $name}}
    				<tr>
						<td class="text-left">{{$key}}</td>
						<td class="text-left">{{$name}}</td>
					</tr>
    				{{/foreach}}
    			</tbody>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">

function deleteRedis() {
    var redisKey = $('#redis_key').val();
    if (redisKey.length == 0) {
        fail('没有输入 Redis KEY', 2);
        return;
    }
    var pageUrl = "{{'Redis/delete'|url}}?key="+redisKey;
    layer.confirm('您确定要删除【' + redisKey + '】吗？', {
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