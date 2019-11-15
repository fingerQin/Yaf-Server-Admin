{% extends "layout.twig" %}
{% block content %}

<div class="container-fluid">
	<div class="info-center">
		<div class="page-header">
			<div class="pull-left">
				<h4>系统错误日志 <span href="javascript:void(0)" style="padding-left: 18px;cursor:pointer;" class="glyphicon glyphicon-refresh mainColor reload" >刷新</span></h4>
			</div>
		</div>

		<div class="table-margin">
			<table class="table table-bordered table-header">
				<thead>
					<tr>
						<th class="w5 text-center">日志 ID</th>
						<th class="w35 text-center">日志内容</th>
						<th class="w10 text-center">日志时间</th>
					</tr>
				</thead>
				<tbody>
					{% for item in list %}
    	            <tr>
						<td class="text-center">{{ item.logid }}</td>
						<td class="text-left">{{ item.content }}</td>
						<td class="text-center">{{ item.c_time }}</td>
					</tr>
					{% endfor %}
					</tbody>
				<tfoot>
				<tr>
					<td colspan="16">
						<div class="pull-right">
							<nav>
								<ul class="pagination">
									{{ pageHtml nofilter|raw }}
								</ul>
							</nav>
						</div>
					</td>
				</tr>
			</tfoot>
		</table>
		</div>
	</div>
</div>

{% endblock %}