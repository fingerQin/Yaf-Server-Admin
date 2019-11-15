{{include file="common/header.php"}}
    <div class="container-fluid">
        <div class="info-center">

        <div style="padding-top: 10px;">
            <div class="columns-mod" >
                <div class="hd cf">
                    <h5>系统信息</h5>
                    <div class="title-opt">
                    </div>
                </div>
                <div class="bd"  style="height:auto;">
                    <div class="sys-info">
                        <table>
                            <tbody><tr>
                                <th>后台管理系统</th>
                                <td>V1.0.0</td>
                            </tr>
                            <tr>
                                <th>服务器操作系统</th>
                                <td>{{$osinfo.os}}  </td>
                            </tr>
                            <tr>
                                <th>系统版本</th>
                                <td>{{$osinfo.php_version}}</td>
                            </tr>
                            <tr>
                                <th>运行环境</th>
                                <td>LNMP</td>
                            </tr>
                            <tr>
                                <th>MYSQL版本</th>
                                <td>5.6</td>
                            </tr>
                            <tr>
                                <th>GD库版本</th>
                                <td>{{$osinfo.gd_info}}</td>
                            </tr>
                            <tr>
                                <th>上传限制</th>
                                <td>{{$osinfo.upload_size}}</td>
                            </tr>
                            <tr>
                                <th>产品设计及研发团队</th>
                                <td>梦想团队</td>
                            </tr>
                            <tr>
                                <th>界面及用户体验团队</th>
                                <td>梦想团队</td>
                            </tr>
                            <tr>
                                <th>官方网址</th>
                                <th>http://www.phpjieshuo.com</th>
                            </tr>
                        </tbody></table>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
{{include file="common/footer.php"}}