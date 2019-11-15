{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>文件列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'File/index'|url}}" method="post">
					<div class="form-group" style="width:415px">
						<span class="pull-left form-span">上传时间:</span>
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
					<span class="pull-left form-span">MD5:</span><input type="text" value="{{$file_md5}}" class="form-control" style="width:200px;" name="file_md5" placeholder="请输入文件MD5值" />
					</div>
					<div class="form-group">
						<select name="user_type" class="form-control">
							<option value="-1">用户类型</option>
							<option {{if $user_type==1}}selected="selected"{{/if}} value="1">管理员</option>
							<option {{if $user_type==2}}selected="selected"{{/if}} value="2">普通用户</option>
						</select>
					</div>
					<div class="form-group">
						<input type="text" value="{{$user_name}}" class="form-control" style="width:200px;" name="user_name" placeholder="请输入要查询的用户账号" />
					</div>
					<div class="form-group">
						<select name="file_type" class="form-control" style="width:120px;">
							<option value="-1">文件类型</option>
							<option {{if $file_type==1}}selected="selected"{{/if}} value="1">图片</option>
							<option {{if $file_type==2}}selected="selected"{{/if}} value="2">其他</option>
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
						<th class="w5 text-center">文件ID</th>
						<th class="w20 text-center">图片</th>
						<th class="w5 text-center">类型</th>
						<th class="w5 text-center">大小</th>
						<th class="w15 text-center">MD5值</th>
						<th class="w5 text-center">用户类型</th>
						<th class="w10 text-center">用户名称</th>
						<th class="w15 text-center">上传时间</th>
						<th class="w10 text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
    				{{foreach $list as $item}}
    				<tr>
						<td class="text-center">{{$item.file_id}}</td>
						<td class="text-center">
							<a target="_blank" href="{{$item.file_name}}"><img width="100" src="{{$item.file_name}}" /></a>
						</td>
						<td class="text-center">{{$item.file_type_label}}</td>
						<td class="text-center">{{$item.file_size}}</td>
						<td class="text-center">{{$item.file_md5}}</td>
						<td class="text-center">{{$item.user_type_label}}</td>
						<td class="text-center">{{$item.user_name}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'File'|access:'delete'}}
							<a href="###" onclick="deleteDialog('deleteFile', '{{'File'|url:'delete':['file_id' => $item.file_id]}}', '图片')" title="删除">删除</a>
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

{{include file="common/footer.php"}}