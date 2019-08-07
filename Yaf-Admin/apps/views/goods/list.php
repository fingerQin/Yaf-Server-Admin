{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>商品列表
				<span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload">刷新</span>
				</h4>
			</div>
			<div class="pull-right">
				{{if 'Goods'|access:'add'}}
				<a type="button" class="btn btn-mystyle btn-sm" href="javascript:void(0);" onclick="add();">添加商品</a>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Goods', 'list');">帮助</button>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Goods/list'|url}}" method="get">
					<div class="form-group">
						<select name="cat_id" id="cat_id" class="form-control">
							<option value="-1">请选择分类</option>
							{{foreach $cat_list as $cat}}
							<option {{if $cat_id==$cat.cat_id}}selected="selected"{{/if}} value="{{$cat.cat_id}}">{{$cat.cat_name}}</option>
							{{foreach $cat.sub as $cat_two}}
							<option {{if $cat_id==$cat_two.cat_id}}selected="selected"{{/if}} value="{{$cat_two.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$cat_two.cat_name}}</option>
							{{foreach $cat_two.sub as $cat_three}}
							<option {{if $cat_id==$cat_three.cat_id}}selected="selected"{{/if}} value="{{$cat_three.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$cat_three.cat_name}}</option>
							{{/foreach}}
							{{/foreach}}
							{{/foreach}}
						</select>
					</div>
					<div class="form-group">
						<span class="pull-left form-span">商品名称</span>
						<input type="text" name="goods_name" class="form-control" style="width: 180px;" value="{{$goods_name}}" placeholder="请输入商品名称查询">
					</div>
					<div class="form-group">
						<span class="pull-left form-span">价格(金币)</span>
						<input type="text" value="{{$start_price}}" class="form-control" name="start_price" style="width: 80px;" placeholder="" />
					</div>
					<div class="form-group">
						<span class="pull-left form-span">~</span>
						<input type="text" value="{{$end_price}}" class="form-control" style="width: 80px;" name="end_price" placeholder="" />
					</div>
					<div class="form-group">
						<span class="pull-left form-span">上下架</span>
						<select name="updown" class="form-control">
							<option {{if $updown==-1}}selected="selected"{{/if}} value="-1">全部</option>
							<option {{if $updown==1}}selected="selected"{{/if}} value="1">上架</option>
							<option {{if $updown==0}}selected="selected"{{/if}} value="0">下架</option>
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
			<table class="table table-bordered table-header" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th class="w5 text-center">商品 ID</th>
						<th class="w10 text-center">商品图片</th>
						<th class="w20 text-center">商品名称</th>
						<th class="w5 text-center">金币</th>
						<th class="w5 text-center">兑换次数</th>
						<th class="w5 text-center">30天兑换次数</th>
						<th class="w5 text-center">上下架</th>
						<th class="w5 text-center">状态</th>
						<th class="w10 text-center">修改时间</th>
						<th class="w10 text-center">创建时间</th>
						<th class="w5 text-center">管理操作</th>
					</tr>
				</thead>
				<tbody>
					{{foreach $list as $item}}
                	<tr>
					<td class="text-center">{{$item.goodsid}}</td>
						<td class="text-center"><img width="120" src="{{$item.goods_img}}" /></td>
						<td class="text-center">{{$item.goods_name}}</td>
						<td class="text-center">
						{{if $item.min_price==$item.max_price}}{{$item.min_price}} 金币{{else}}{{$item.min_price}}~{{$item.max_price}} 金币{{/if}}
						</td>
						<td class="text-center">{{$item.buy_count}}</td>
						<td class="text-center">{{$item.month_buy_count}}</td>
						<td class="text-center">{{if $item.marketable}}上架中{{else}}已下架{{/if}}</td>
						<td class="text-center">{{if $item.status==1}}正常{{elseif $item.status==2}}已删{{else}}无效{{/if}}</td>
						<td class="text-center">{{$item.u_time}}</td>
						<td class="text-center">{{$item.c_time}}</td>
						<td class="text-center">
							{{if 'Goods'|access:'edit'}}
							<a href="###" onclick="edit({{$item.goodsid}}, '{{$item.goods_name|escape}}')">修改</a> |
							{{/if}}
							{{if 'Goods'|access:'delete'}}
							<a href="###" onclick="deleteDialog('deleteGoods', '{{'Goods'|url:'delete':['goods_id' => $item.goodsid]}}', '{{$item.goods_name}}')">删除</a>
							{{/if}}
						</td>
					</tr>
                	{{/foreach}}
                </tbody>
				<tfoot>
					<tr>
						<td colspan="16">
							<div class="pull-right page-block">
								<nav><ul class="pagination">{{$page_html}}</ul></nav>
							</div>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
function edit(id, name) {
	var title = '修改『' + name + '』';
	var page_url = "{{'Goods/edit'|url}}?goods_id="+id;
	postDialog('editGoods', page_url, title, 900, 800, true);
}
function add() {
	postDialog('addGoods', '{{'Goods'|url:'add'}}', '添加管理员', 900, 800, true);
}
</script>

{{include file="common/footer.php"}}