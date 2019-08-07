{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>订单列表 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
			<div class="pull-right">
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Order', 'list');">帮助</button>
			</div>
		</div>
		<div class="search-box row">
			<div class="col-md-12">
				<form action="{{'Order/list'|url}}" onsubmit="return submitBefore();" method="get">
					<div class="form-group">
						<span class="pull-left form-span">手机账号</span>
						<input type="text" name="mobile" id="mobile" class="form-control" style="width: 180px;" value="{{$mobile|escape}}" placeholder="请输入手机账号">
                    </div>
                    <div class="form-group">
						<span class="pull-left form-span">订单号</span>
						<input type="text" name="order_sn" id="order_sn" class="form-control" style="width: 180px;" value="{{$order_sn|escape}}" placeholder="请输入手机账号">
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
						<th class="w5 text-center">订单 ID</th>
						<th class="w10 text-center">订单号</th>
						<th class="w5 text-center">订单总金币</th>
						<th class="w10 text-center">支付时间</th>
						<th class="w5 text-center">订单状态</th>
						<th class="w10 text-center">下单时间</th>
						<th class="w25 text-center">商品列表*数量</th>
						<th class="w15 text-center">物流信息</th>
						<th class="w10 text-center">操作</th>
					</tr>
				</thead>
				<tbody>
					{{foreach $list as $item}}
    				<tr>
						<td align="center">{{$item.orderid}}</td>
						<td align="center">{{$item.order_sn}}</td>
                        <td align="center">{{$item.total_price}}</td>
						<td align="center">{{$item.pay_time}}</td>
						<td align="center">{{$item.order_status_label}}</td>
						<td align="center">{{$item.c_time}}</td>
						<td align="center">
							{{foreach $item.goods_list as $goods}}
							<p>
								<img width="180" title="{{$goods.goods_name}}" src="{{$goods.goods_image}}" alt="{{$goods.goods_name}}" /><span style="font-weight:bold;">x {{$goods.quantity}}</span>
							</p>
							{{/foreach}}
						</td>
						<td align="center">{{$item.c_time}}</td>
                        <td align="center">
                            <a href="###" onclick="audit({{$item.orderid}}, '{{$item.order_sn}}')">发货</a>
                            <a href="###" onclick="details({{$item.orderid}}, '{{$item.order_sn}}')">关闭</a><br />
                            <a href="###" onclick="details({{$item.orderid}}, '{{$item.order_sn}}')">修改收货地址</a>
                        </td>
					</tr>
					{{/foreach}}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="16">
							<div class="pull-right page-block">
								<nav>
									<ul class="pagination">{{$page_html}}</ul>
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
function deliver(id, name) {
	var title = '您正在对订单『' + name + '』进行发货';
	var page_url = "{{'Order/deliverGoods'|url}}?orderid="+id;
	postDialog('auditCoupon', page_url, title, 450, 300);
}
function close(id, name) {
	var title = '您确定关闭『' + name + '』吗？';
	var page_url = "{{'Coupon/close'|url}}?logid="+id;
	postDialog('auditDetails', page_url, title, 1000, 600, true);
}
</script>

{{include file="common/footer.php"}}