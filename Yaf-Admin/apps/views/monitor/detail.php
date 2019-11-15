{{include file="common/header.php"}}

<div class="main">
	<form action={{'Config/add'|url}} method="post" name="myform" id="myform">
		<table cellpadding="2" cellspacing="1" class="content" width="100%">
			<tbody>
				<tr>
					<th class="left-txt">ID:</th>
					<td>{{$detail.id}}</td>
				</tr>
				<tr>
					<th class="left-txt">流水号:</th>
					<td>{{$detail.serial_no}}</td>
				</tr>
				<tr>
					<th class="left-txt">监控编码:</th>
					<td>{{$detail.code_label}}</td>
				</tr>
				<tr>
					<th class="left-txt">处理状态:</th>
					<td>{{$detail.status_label}}</td>
				</tr>
				<tr>
					<th class="left-txt">处理备注：</th>
					<td>{{$detail.remark}}</td>
				</tr>
				<tr>
					<td colspan="2"><textarea readonly="readonly" style="height:200px;width:95%;margin-left:10px;">{{$detail.data}}</textarea></td>
				</tr>
			<tbody>
		</table>

	</form>
</div>

{{include file="common/footer.php"}}