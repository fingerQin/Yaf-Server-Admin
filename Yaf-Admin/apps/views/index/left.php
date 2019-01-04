{{foreach $left_menu as $menu}}
    <div class="sBox">
        <div class="subNav sublist-up" style="padding-left: 12px;">
            <span class="sublist-title">{{$menu.menu_name}}</span>
            <span class="title-icon glyphicon glyphicon-chevron-up" style="float: right;"></span>
        </div>
        <ul class="navContent" style="display:none">
            {{foreach $menu.sub_menu as $sub_item}}
                <li class="side-active">
                    <div class="showtitle" style="width:100px;"><img src="" />{{$sub_item.menu_name}}</div>
                    <a href="{{$sub_item.c|url:$sub_item.a:['menu_id' => $sub_item.menuid]}}&{{$sub_item.ext_param}}" target="right" class='juli'><span class="span-icon {{if $sub_item.icon != '' }}{{$sub_item.icon}}{{else}}glyphicon-plus{{/if}}"></span><span class="sub-title">{{$sub_item.menu_name}}</span></a>
                </li>
            {{/foreach}}
        </ul>
    </div>
{{/foreach}}
<script type="text/javascript">
    $(function(){
        $(".navContent li.side-active ").on('click',function(){
            $('.navContent li').removeClass('active')
            $(this).addClass('active')
        });
        
    });
</script>
<style>
    
</style>
{{*
图标中心
http://v3.bootcss.com/components/
*}}