define("Form",function (require, exports, module) {
    var _rules = {
        username:{exp:function(value){
            var length=value.replace(/[^\x00-\xff]/g,"***").length;
            if(length==0){
                return "请输入用户名";
            }else if(length<4||length>15){
                return "用户名长度只能在4-15位字符之间或5个中文以内";
            }else if((/^[0-9]/).test(value)){
                return "不可以数字开头";
            }else if((/\s+|^c:\\con\\con|[@,%,\*\"\s\<\>\&]|\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8/i).test(value)){
                return "不能包含特殊字符串";
            }
            return true;
        }},
        password:{
            exp:function(value){
                var length=value.length;
                if(length>16||length<6){
                    return '密码长度为6到16位';
                }else {
                    return true;
                }
            }
        },
        chinese:{exp:/^[\\u4e00-\\u9fa5]+$/,msg:"请输入中文汉字"},
        number:{exp:/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/,msg:"请输入有效的数字"},
        money:{exp:/^(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/,msg:"请正确输入金额"},
        qq: {exp:/^[1-9]*[1-9][0-9]*$/,msg:"请输入正确的QQ号码"},
        telephone:{exp:/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/,msg:'固定电话格式有误'},
        mobilephone:{exp:/^0?(13|15|17|18|14)[0-9]{9}$/,msg:'手机号码格式有误'},
        datetime:{exp:/^\d{4}(-\d{2}){2}(\s\d{2}(:\d{2}){2})?$/,msg:'时间格式有误'},
        zip:{exp:/^[1-9]\d{5}$/,msg:'邮政编码有误'},
        tel: {exp: /^0?(13|15|17|18|14)[0-9]{9}$/, msg: "请输入正确的手机号码"},
        email: {exp: function(value){
            if(value=="")return "请输入邮箱";
            return /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/.test(value);
        }, msg: "请输入正确的邮箱格式"},
        url:{exp:function(value){
            if(value=="")return "请输入url，请去除空格";
            return /^(http(s)?:\/\/)?(\w+)(\.\w+){1,}$/.test(value);
        },msg:"请输入格式为http://xxx.xxx.xxx的url"},
        "required": {exp: /\S+/, msg: "此选项必填"}
    }
    function validateValue(value,rule,event,formData) {
        if (!rule || (typeof(rule) == "object" && !(rule instanceof RegExp)))rule = _rules["required"].exp;
        switch (typeof(rule)) {
            case "string":
                if(rule in _rules){
                    return validateValue.call(this,value,_rules[rule].exp,event,formData);
                }else{
                    rule = new RegExp(rule);
                }
                break;
            case "function":
                return rule.call(this,value,event,formData);
                break;
        }
        return rule.test(value);
    }

    function serializeObject(serializeArray){
        var data={},lookup=data;
        function parseInput(name,value){
            var named = name.replace(/\[([^\]]+)?\]/g, ',$1').split(','),
                cap = named.length - 1,
                i = 0;
            for ( ; i < cap; i++ ) {
                lookup = lookup[ named[i] ] = lookup[ named[i] ] || ( named[i+1] == "" ? [] : {} );
            }
            lookup.length != undefined?lookup.push( value ):lookup[ named[ cap ] ]  = value;
            lookup = data;
        }
        if(typeof(serializeArray)=="object"){
            if(serializeArray instanceof Array){
                for(var i=0;i<serializeArray.length;i++){
                    if(serializeArray[i].name.indexOf("[")>-1){
                        parseInput(serializeArray[i].name,serializeArray[i].value);
                    }else{
                        data[serializeArray[i].name]=serializeArray[i].value;
                    }
                }
            }else {
                for(var name in serializeArray){
                    serializeArray[name].name=name;
                    if(name.indexOf("[")>-1){
                        parseInput(name,serializeArray[name]);
                    }else{
                        data[name]=serializeArray[name];
                    }
                }
            }
        }
        return data;
    }

    function objects2Array(object){
        var firstKey = Object.keys(object)[0];
        var array = [];
        for(var i=0;i<object[firstKey].length;i++){
            var item = {};
            for(var key in object){
                item[key] = object[key][i];
            }
            array.push(item);
        }
        return array;
    }

    function check(form, rules,event) {
        if (typeof(rules) == "undefined")rules = {};
        form = $(form);
        var data = form.serializeArray();
        var _error = [];
        for (var i in data) {
            var name = data[i]["name"];
            if (typeof(rules[name]) == "object") {
                var value = data[i]["value"],input = form.find("[name='" + name + "']"),msg=rules[name].msg || (rules[name].exp in _rules?_rules[rules[name].exp].msg:null);
                if(rules[name].allowEmpty==true&&value=="")continue;
                var result=validateValue.call(form,value, rules[name].exp,event,data);
                if (typeof(result)=="string")msg=result,result=false;
                if (result===false)_error.push({msg: msg, ui: input.get(0)});
            }
        }
        if (_error.length) {
            return {status: false,errors:_error};
        }
        return {status: true, data: data};
    }

    function placeholderHack(form, type) {
        if (typeof(form) == "undefined")form = "body";
        if (typeof(type) == "undefined")type = 1;
        form = $(form);
        function isPlaceholder() {
            var input = document.createElement('input');
            return 'placeholder' in input;
        }

        form.find("[placeholder]").each(function () {
            var $this=$(this);
            var placeholder = $this.attr("placeholder");
            if(!isPlaceholder())$this.wrap("<div class='placeholder_wrap'></div>").after("<span class='placeholder_label'>" + $(this).attr("placeholder") + "</span>")
            if (type == 2) {
                if(!isPlaceholder())$this.keyup(function () {
                    this.value != ""?$this.next().empty(): $this.next().html(placeholder);
                });
            } else {
                $this.focus(function () {
                    !isPlaceholder() ? $this.next().empty() : $this.attr("placeholder", "");
                }).blur(function () {
                    if (this.value == "")!isPlaceholder() ? $this.next().html(placeholder) : $this.attr("placeholder", placeholder);
                });
            }
            $this.next().click(function(){
                $this.focus();
            });
        });
        return exports;
    }
    function eventCall(fun,event){
        if(typeof(fun)=="function"){
            event.ui=this;
            return fun.call(this,event);
        }
    }
    function validateForm ($form, rules, options) {
        var map = [],errors=[],errorInput=[],_default={};
        options= $.extend({},_default,options);
        function addErrorUi(input,event){
            var index=jQuery.inArray(input,errorInput);
            if(index==-1){
                errorInput.push(input),errors.push(event);
            }else{
                errors[index]=event;
            }
        }
        function deleteErrorUi(input){
            errorInput=jQuery.grep(errorInput,function(item){
                return item!==input
            });
            errors=jQuery.grep(errors,function(item){
                return item.ui!==input
            });
        }
        for (var name in rules) map.push("[name='" + name + "']");

        var serverUniqueCache = {};
        function actionCheckFunc(event) {
            if(!this.name)return false;
            var _this=this,_name=this.name,_value=$(this).val(),msg=rules[_name].msg || (rules[_name].exp in _rules?_rules[rules[_name].exp].msg:null);
            if(!serverUniqueCache[_name])serverUniqueCache[_name]={};
            if(event.type=="blur"||event.type=="change")eventCall.call(_this,options["reset"],event);
            if (_value != "") {
                var result=validateValue.call(_this,_value, rules[_name].exp,event);
                if (typeof(result)=="string"){msg=result;result=false;}
                if (result===false) {
                    event.msg=msg;addErrorUi(_this,event);
                    eventCall.call(_this,options["error"],event);
                } else if(rules[_name]["unique"]){
                    var unique=rules[_name]["unique"];
                    if(typeof(unique)!="object"){
                        unique={name:_name,url:unique,result:false}
                    }else{
                        unique=$.extend({name:_name,result:false},unique);
                    }
                    var postData={};postData[unique["name"]]=_value;
                    if(!(_value in serverUniqueCache[_name])){
                        $.ajax({
                            type: 'POST',
                            url: unique.url,
                            data: postData,
                            success: function(data){
                                if(data.errcode==unique["result"]){
                                    deleteErrorUi(_this);
                                    eventCall.call(_this,options["success"],event);
                                }else{
                                    serverUniqueCache[_name][_value]=data.errmsg;
                                    event.msg=serverUniqueCache[_name][_value];addErrorUi(_this,event);
                                    eventCall.call(_this,options["error"],event);
                                }
                            },
                            error:function(){
                                deleteErrorUi(_this);
                                eventCall.call(_this,options["httpFault"]||options["success"],event);
                            },
                            dataType: "json"
                        });
                    }else{
                        event.msg=serverUniqueCache[_name][_value];addErrorUi(_this,event);
                        eventCall.call(_this,options["error"],event);
                    }
                }else if(result===true){
                    deleteErrorUi(_this);
                    eventCall.call(this,options["success"],event);
                }
            }else{
                deleteErrorUi(_this);
            }
        }

        function bindInputAction(event){
            var element=this,name=element.name;
            if($(element).data('formBindInputAction'))return;
            var index = $.inArray("[name='" + name + "']",map);
            if(index>-1)map.splice(index,1);
            var actionControl={
                focus: function (event) {
                    eventCall.call(this,options["reset"],event);
                    eventCall.call(this,options["focus"],event);
                }
            };
            if($(element).is('[type=text]')||$(element).is('textarea')){
                if(rules[name].type){
                    actionControl.change=actionCheckFunc
                }else{
                    actionControl.blur=actionCheckFunc;
                }
            }else{
                actionControl.change=actionCheckFunc;
            }
            $(element).bind(actionControl).data('formBindInputAction',1);
        }

        $form.submit(function(event){
            if(errors.length>0){
                if(typeof(options["submit"])=="function"){
                    event.result={stauts:false,errors:errors};
                    return eventCall.call(this,options["submit"],event);
                }else{
                    for(var i=0;i<errors.length;i++){
                        eventCall.call(errorInput[i],options["error"],errors[i]);
                    }
                }
                return false;
            }
            var result=check($form,rules,event);event.result=result;
            if(typeof(options["submit"])=="function"){
                return eventCall.call(this,options["submit"],event);
            }else{
                if(!result.status){
                    for(var i=0;i<result.error.length;i++){
                        var error=result.error[i];
                        event.ui=error.ui;event.msg=error.msg;
                        eventCall.call(error.ui,options["error"],event);
                    }
                }
                return result.status;
            }
        }).find(map.join(",")).each(bindInputAction);
        if(map.length>0){
            $form.on('mouseover',map.join(','),bindInputAction);
        }
        return exports;
    }
    function getPwdLevel(value) {
        var pattern_1 = /^.*([\W_])+.*$/i;
        var pattern_2 = /^.*([a-zA-Z])+.*$/i;
        var pattern_3 = /^.*([0-9])+.*$/i;
        var level = 0;
        if (value.length > 10) level++;
        if (pattern_1.test(value)) level++;
        if (pattern_2.test(value)) level++;
        if (pattern_3.test(value)) level++;
        if (level > 3) level = 3;
        return level;
    }
    function limitClick(button,callback,time,classToggle){
        time=time||60;
        var countdown_time=0;
        function countdown(progress,complete){
            var current= 0,$this=button,originLabel=button.val(),
                label=($this.attr("disabled-label")||'{$time}秒后重发').split("{$time}"),
                repeatLabel=$this.attr("repeat-label");
            if(classToggle)$this.addClass(classToggle);
            $this.attr("disabled",true);
            if(label.length>0)$this.val(label.join(time));
            var st=setInterval(function(){
                current++;
                if(label.length>0)$this.val(label.join(time-current));
                if(current==time){
                    clearInterval(st);
                    if(classToggle)$this.removeClass(classToggle);
                    $this.removeAttr("disabled");
                    $this.val(repeatLabel||originLabel);
                    if(typeof(complete)=="function")complete();
                }else if(typeof(progress)=="function")progress(time-current);
            },1000);
            countdown_time++;
            return st;
        }
        button.click(function(event){
            if(callback.apply(this,[countdown,countdown_time+1])!==false){
                countdown();
            }
            return false;
        });
        return countdown;
    }
    function getRegExpRule(name){
        return name=="email"?/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/:RegExp(_rules[name].exp);
    }

    function selectors(options){
        var _defaults={
            url:'',
            name:'',
            container:'',
            class:'',
            level:0,
            defaults:[],
            change:null
        };
        options = $.extend({},_defaults,options);
        var Http = require('Http');
        var url = options.url,
            className = options.class,
            name = options.name,
            level = options.level,
            defaults=options.defaults,
            params = options.params||{};
        var $container = $(options.container);
        var _eleSelector='select.js-form-select';

        function getResource(pid){
            params.pid  = pid;
            var dtd = $.Deferred();
            Http.get({
                url:url,
                data:params,
                cache:true,
                success:function(response){
                    dtd.resolve(response.data);
                },error:function(response){
                    dtd.reject(response.data);
                }
            });
            return dtd.promise();
        }
        function createOption(options,defaultLabel){
            options = options || [];
            defaultLabel = defaultLabel || '请选择';
            var optionArr = [['<option value="">','</option>'].join(defaultLabel)];
            for(var i=0;i<options.length;i++){
                optionArr.push(['<option value="'+options[i].id+'">','</option>'].join(options[i].label));
            }
            return optionArr.join('');
        }
        function createSelect(sdata){
            var selectWrap = ['<select class="js-form-select '+className+'" name="'+name+'">','</select>'];
            var $select = $(selectWrap.join(createOption(sdata)));
            $select.change(function(){
                var $this=$(this),value=$this.val();
                if($this.next().is(_eleSelector)){
                    //有子菜单
                    //加载中状态
                    if(level){
                        //限级：子菜单禁止状态
                        $this.nextAll(_eleSelector).each(function(i){
                            var _$this = $(this);
                            if((_$this.index())<level){
                                _$this.attr('disabled',true);
                            }
                        });
                    }else{
                        //不限级：子菜单移除
                        $this.nextAll(_eleSelector).each(function(i){
                            $(this).remove();
                        });
                        $container.find(_eleSelector).filter(':last').attr('name',name);
                    }
                    if(value)getResource(value).done(function(response){
                        var options = createOption(response);
                        if(level){
                            //限级：子菜单数据填充
                            $this.next().html(options).attr('disabled',false).nextAll(_eleSelector).each(function(i){
                                var _$this = $(this);
                                if((_$this.index())<level){
                                    _$this.html(createOption()).attr('disabled',false);
                                }
                            });
                        }else{
                            //不限级：有数据创建子菜单
                            if(response&&response.length)containerAddSelect(response);
                        }
                    })
                }else if(!level){
                    if(value)getResource(value).done(function(response){
                        if(response&&response.length)containerAddSelect(response);
                    })
                }
                if(typeof(options.change) == 'function')options.change.call(this,value);
            });
            return $select;
        }

        var st;
        function containerAddSelect(response){
            var $selector = createSelect(response);
            $container.append($selector);
            clearTimeout(st);
            st = setTimeout(function(){
                $container.find(_eleSelector).removeAttr('name').filter(':last').attr('name',name);
            },200);
            return $selector;
        }

        //菜单初始化
        function init(){
            getResource(0).done(function(response){
                var $select = containerAddSelect(response);
                $container.append($select);
                //限级，自动创建菜单占位
                if(level){
                    for(var i=1;i<level;i++)containerAddSelect();
                }else if(defaults.length>0){
                    //不限级，根据数据创建回显菜单占位
                    for(var i=0;i<(defaults.length-1);i++)containerAddSelect();
                }
                //数据回显
                if(defaults.length>0){
                    $select.val(defaults[0]);
                    var defers = [];
                    //回显数据级数，如不限级或回显示级在限级内，则获取所有预设下级资源
                    var len = (!level||defaults.length<level)?defaults.length:(level>defaults.length?defaults.length:(level-1));
                    //根据计算级数加入获取队列
                    for(var i=0;i<len;i++)defers.push(getResource(defaults[i]));

                    $.when.apply(null,defers).done(function(){
                        for(var i=0;i<len;i++){
                            if(typeof(defaults[i+1])!='undefined')$container.find(_eleSelector).eq(i+1).html(createOption(arguments[i])).val(defaults[i+1]);
                        }
                    });
                }
            });
        }

        function reset(newParam){
            $.extend(params,newParam);
            $container.empty();
            init();
        }

        init();
        this.reset = reset;
    }

    function autoComplete(options){
        var _defaults={
            input:"<input />",
            url:"",
            tpl:null
        };
        options= $.extend({},_defaults,options);
        var $input=$(options.input),$tips=$("<div class='rel' style='display: none;'><div class='tips-list'><ul class='cc'></ul></div></div>");
        $input.wrap("<div class='form-auto-complete'></div>").after($tips);
        $input.parents('form').attr('autocomplete','off');
        $tips.on('click','li a',function(){
           var text = $(this).text();
            $input.val(text);
            $tips.hide().find("ul").empty();
        });
        var $context=$input.parent();
        !function(){
            var last_value,tips_st,blur_st;
            $input.keyup(function(event){
                clearTimeout(tips_st);
                var keyword=$(this).val();
                if (keyword == ""){
                    $tips.hide().find("ul").empty();
                    return;
                } else if($tips.is(":visible")&&(event.keyCode==38||event.keyCode==40)){
                    var search_bar_li=$tips.find("li"),total=search_bar_li.size(),current=search_bar_li.filter("li.active"),index=current.index();
                    var d_value=event.keyCode-39;
                    var nextIndex=index+d_value,label;
                    search_bar_li.removeClass("active");
                    if(nextIndex==-2){
                        label=search_bar_li.filter(":last").addClass("active").text();
                    }else if(nextIndex>-1&&nextIndex<total){
                        label=search_bar_li.eq(nextIndex).addClass("active").text();
                    }else{
                        label=last_value;
                    }
                    $(this).val(label);
                    return;
                }else if(keyword==last_value){
                    return;
                }
                function tpl(data){
                    var html=[];
                    for(var i=0;i<data.title.length;i++){
                        html.push("<li><a href='"+data.url[i]+"'>"+data.title[i]+"</a></li>");
                    }
                    return html.join("");
                }
                var Http = require('Http');
                tips_st=setTimeout(function(){
                    Http.get({
                        url:options.url,
                        data:{keyword:keyword},
                        success:function(data){
                            last_value = keyword;
                            if(data.data){
                                $tips.find("ul").html(typeof(options.render)=="function"?options.render(data.data):tpl(data.data));
                                $tips.show();
                            }else{
                                $tips.hide().find("ul").empty();
                            }
                        },
                        cache:true
                    })
                },300)
            }).focus(function(){
                $tips.show()
            });
            $(document).click(function(event){
                var $findTips=$(event.target).closest($context);
                $tips[$findTips.size()?"show":"hide"]();
            });
        }();
    }

    function setData($form,formData){
        var key,value,tagName,type,arr;
        for(var x in formData){
            key = x;
            value = formData[x];
            $form.find("[name='"+key+"'],[name='"+key+"[]']").each(function(){
                tagName = $(this)[0].tagName;
                type = $(this).attr('type');
                if(tagName=='INPUT'){
                    if(type=='radio'){
                        $(this).attr('checked',$(this).val()==value);
                    }else if(type=='checkbox'){
                        arr = value.split(',');
                        for(var i =0;i<arr.length;i++){
                            if($(this).val()==arr[i]){
                                $(this).attr('checked',true);
                                break;
                            }
                        }
                    }else{
                        $(this).val(value);
                    }
                }else if(tagName=='SELECT' || tagName=='TEXTAREA'){
                    $(this).val(value);
                }

            });
        }
    }

    exports.selectors=selectors;
    exports.limitClick=limitClick;
    exports.autoComplete=autoComplete;
    exports.serializeObject=serializeObject;
    exports.objects2Array=objects2Array;
    exports.getPwdLevel=getPwdLevel;
    exports.check=check;
    exports.placeholderHack=placeholderHack;
    exports.validateForm =validateForm;
    exports.getRegExpRule =getRegExpRule;
    exports.setData =setData;
})