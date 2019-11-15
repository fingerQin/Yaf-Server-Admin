{{include file="common/header.php"}}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>分类列表</h4>
			</div>
			<div class="pull-right">
				{{if 'Category'|access:'add'}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="add();">添加分类</button>
				{{/if}}
				<button type="button" class="btn btn-mystyle btn-sm" onclick="helpDialog('Category', 'index');">帮助</button>
			</div>
		</div>
		<div class="clearfix"></div>

		<div class="search-box row">
			<div class="col-md-12">
				<form name="searchform" action="" method="get" id="searchform">
					<div class="form-group">
						<select id="cat_type" name="cat_type" class="form-control" style="width:200px;">
							{{foreach $cat_type_list as $type_id => $type_name}}
							<option {{if $type_id==$cat_type}}selected="selected"{{/if}} value="{{$type_id}}">{{$type_name}}</option>
							{{/foreach}}
						</select>
					</div>
				</form>
			</div>
		</div>

		<div class="table-margin">
			<form name="myform" action="{{'Category/sort'|url}}" method="post" id="sort_form">
				<table class="table table-bordered table-header" width="100%" cellspacing="0" style="margin-bottom:5px;">
					<thead>
						<tr>
							<th class="text-center">排序</th>
							<th class="text-center">分类id</th>
							<th class="text-center">分类名称</th>
							<th class="text-center">是否外链</th>
							<th class="text-center">是否显示</th>
							<th class="text-center">分类类型</th>
							<th class="text-center">code 编码</th>
							<th class="text-center">管理操作</th>
						</tr>
					</thead>
					<tbody>
						{{foreach $list as $cat}}
							<tr>
							<td class="text-center">
								<input name='listorders[{{$cat.cat_id}}]' type='text' size='3' value='{{$cat.listorder}}' class='input-text-c text-center'>
							</td>
							<td class="text-center">{{$cat.cat_id}}</td>
							<td class="text-left">{{$cat.cat_name}}</td>
							<td class="text-center">{{if $cat.is_out_url}}是{{else}}否{{/if}}</td>
							<td class="text-center">{{if $cat.display}}是{{else}}否{{/if}}</td>
							<td class="text-center">{{$cat_type_list[$cat.cat_type]}}</td>
							<td class="text-center">{{$cat.cat_code}}</td>
							<td class="text-center">
								{{if 'Category'|access:'add'}}
								<a href="javascript:postDialog('addCategory', '{{'Category/add'|url}}?parentid={{$cat.cat_id}}', '添加子分类', 450, 380);">添加子分类</a>
								| 
								{{/if}}

								{{if 'Category'|access:'edit'}}
								<a href="javascript:postDialog('editCategory', '{{'Category/edit'|url}}?cat_id={{$cat.cat_id}}', '修改子分类', 450, 400);">修改</a>
								| 
								{{/if}}

								{{if 'Category'|access:'delete'}}
								<a href="javascript:deleteDialog('deleteCategory', '{{'Category/delete'|url}}?cat_id={{$cat.cat_id}}', '{{$cat.cat_name}}');">删除</a>
								{{/if}}
							</td>
						</tr>
							{{if isset($cat.sub)}}
							{{foreach $cat.sub as $sub_m}}
							<tr>
							<td class="text-center">
								<input name='listorders[{{$sub_m.cat_id}}]' type='text' size='3' value='{{$sub_m.listorder}}' class='input-text-c text-center'>
							</td>
							<td class="text-center">{{$sub_m.cat_id}}</td>
							<td class="text-left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─ {{$sub_m.cat_name}}</td>
							<td class="text-center">{{if $sub_m.is_out_url}}是{{else}}否{{/if}}</td>
							<td class="text-center">{{if $sub_m.display}}是{{else}}否{{/if}}</td>
							<td class="text-center">{{$cat_type_list[$sub_m.cat_type]}}</td>
							<td class="text-center">{{$sub_m.cat_code}}</td>
							<td class="text-center">
								{{if 'Category'|access:'add'}}
								<a href="javascript:postDialog('addCategory', '{{'Category/add'|url}}?parentid={{$sub_m.cat_id}}', '添加子分类', 450, 380);">添加子分类</a>
								| 
								{{/if}}

								{{if 'Category'|access:'edit'}}
								<a href="javascript:postDialog('editCategory', '{{'Category/edit'|url}}?cat_id={{$sub_m.cat_id}}', '修改子分类', 450, 400);">修改</a>
								| 
								{{/if}}

								{{if 'Category'|access:'delete'}}
								<a href="javascript:deleteDialog('deleteCategory', '{{'Category/delete'|url}}?cat_id={{$sub_m.cat_id}}', '{{$sub_m.cat_name}}');">删除</a>
								{{/if}}
							</td>
						</tr>
							{{if isset($sub_m.sub)}}
							{{foreach $sub_m.sub as $ss_m}}
							<tr>
							<td align='center'>
								<input name='listorders[{{$ss_m.cat_id}}]' type='text' size='3' value='{{$ss_m.listorder}}' class='input-text-c text-center'>
							</td>
							<td align='center'>{{$ss_m.cat_id}}</td>
							<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─ {{$ss_m.cat_name}}</td>
							<td align='center'>{{if $ss_m.is_out_url}}是{{else}}否{{/if}}</td>
							<td align='center'>{{if $ss_m.display}}是{{else}}否{{/if}}</td>
							<td align='center'>{{$cat_type_list[$ss_m.cat_type]}}</td>
							<td align='center'>{{$ss_m.cat_code}}</td>
							<td align='center'>
								{{if 'Category'|access:'add'}}
								<a href="javascript:postDialog('addCategory', '{{'Category/add'|url}}?parentid={{$ss_m.cat_id}}', '添加子分类', 450, 380);">添加子分类</a>
								| 
								{{/if}}

								{{if 'Category'|access:'edit'}}
								<a href="javascript:postDialog('editCategory', '{{'Category/edit'|url}}?cat_id={{$ss_m.cat_id}}', '修改子分类', 450, 400);">修改</a>
								| 
								{{/if}}

								{{if 'Category'|access:'delete'}}
								<a href="javascript:deleteDialog('deleteCategory', '{{'Category/delete'|url}}?cat_id={{$ss_m.cat_id}}', '{{$ss_m.cat_name}}');">删除</a>
								{{/if}}
							</td>
						</tr>
						{{/foreach}}
						{{/if}}
						{{/foreach}}
						{{/if}}
						{{/foreach}}
					</tbody>
				</table>
				{{if 'Category'|access:'sort'}}
				<div class="btn" style="padding-left:0px;"><input type="button" id="form_submit" class="button" name="dosubmit" value="排序" /></div>
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
            url: $('#sort_form').attr('action'),
            dataType: 'json',
            data: $('#sort_form').serialize(),
            success: function(data) {
                if (data.code == 200) {
					success(data.msg, 1, '{{'Category/index'|url}}');
                } else {
                	fail(data.msg, 3);
                }
            }
	    });
	});
	$('#cat_type').change(function(){
		$('#searchform').submit();
	});
});

function add() {
	postDialog('addCategory', '{{'Category'|url:'add':['cat_type' => $cat_type]}}', '添加分类', 400, 370);
}

</script>

{{include file="common/footer.php"}}