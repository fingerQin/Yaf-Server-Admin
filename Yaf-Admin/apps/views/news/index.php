{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>文章列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				{{if 'News'|access:'add'}}
				<a type="button" class="btn btn-mystyle btn-sm" href="{{'News/add'|url}}">添加文章</a>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('News', 'index');">帮助</button>
			</div>
		</div>

		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'News/index'|url}}" method="post">
					<div class="form-group">
						<span class="pull-left form-span">文章标题</span>
						<input type="text" name="title" class="form-control" style="width: 180px;" value="{{$title}}" placeholder="请输入文章标题">
					</div>
					<div class="form-group" style="width:415px;">
						<span class="pull-left form-span">添加时间</span>
						<input type="text" name="starttime" id="starttime" value="{{$starttime}}" size="20" class="date form-control" style="display:inline;width:160px;" /> ～ 
						<input type="text" name="endtime" id="endtime" value="{{$endtime}}" size="20" class="date form-control" style="display:inline;width:160px;" />
						<script type="text/javascript">
						Calendar.setup({
							weekNumbers: false,
							inputField : "starttime",
							trigger    : "starttime",
							dateFormat: "%Y-%m-%d %H:%I:%S",
							showTime: true,
							minuteStep: 1,
							onSelect   : function() {this.hide();}
						});

						Calendar.setup({
							weekNumbers: false,
							inputField : "endtime",
							trigger    : "endtime",
							dateFormat: "%Y-%m-%d %H:%I:%S",
							showTime: true,
							minuteStep: 1,
							onSelect   : function() {this.hide();}
						});
						</script>
					</div>
					<div class="form-group">
						<span class="pull-left form-span">管理员</span>
						<input type="text" name="admin_name" class="form-control" style="width: 120px;" value="{{$admin_name}}" placeholder="请输入管理员账号">
					</div>
					<div class="form-group">
						<button type="submit" class="form-control btn btn-info" ><span class="glyphicon glyphicon-search"></span> 查询 </button>
					</div>
				</form>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="table-margin">
			<table class="table table-bordered table-header" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th class="w5 text-center">文章ID</th>
						<th class="w15 text-center">图片</th>
						<th class="w15 text-center">文章标题</th>
						<th class="w10 text-center">分类</th>
						<th class="w5 text-center">显示</th>
						<th class="w5 text-center">浏览量</th>
						<th class="w10 text-center">修改时间</th>
						<th class="w10 text-center">创建时间</th>
						<th class="w5 text-center">创建人</th>
						<th class="w10 text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
				{{foreach $list as $item}}
					<tr>
						<td align="center">{{$item.news_id}}</td>
						<td align="center">
							<a target="_blank" href="{{$item.image_url}}">
								<img width="200" height="150" src="{{$item.image_url}}" />
							</a>
						</td>
						<td class="text-center">{{$item.title}}</td>
						<td class="text-center">{{$item.cat_name}}</td>
						<td class="text-center">{{if $item.display}}显示{{else}}隐藏{{/if}}</td>
						<td class="text-center">{{$item.hits}}</td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">{{$item.mobile}}<br/>{{$item.real_name}}</td>
						<td class="text-center">
							{{if 'News'|access:'edit'}}
							<a href="{{'News'|url:'edit':['news_id' => $item.news_id]}}" title="修改">修改</a> | 
							{{/if}}
							{{if 'News'|access:'delete'}}
							<a href="###" onclick="deleteDialog('deleteDelete', '{{'News'|url:'delete':['news_id' => $item.news_id]}}', '{{$item.title}}')" title="删除">删除</a>
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
									<ul class="pagination">
										{{$page_html}}
									</ul>
								</nav>
							</div>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</form>
</div>

{{include file="common/footer.php"}}