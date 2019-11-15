define("Http",function(require,exports,module){
    var feedback=require("Feedback"),inOperation={};
    var cache={};
    function post(options){
        options= $.extend({type:"post",success:function(response){
            feedback.message(response.errmsg);
        },fault:function(response){
            feedback.message(response.errmsg);
        },error:function(response){
            feedback.message(response.errmsg);
        }},options);
        return ajax(options);
    }
    function get(options){
        options= $.extend({type:"get",once:false},options);
        return ajax(options);
    }
    function ajax(options){
        var _default={
            url:"",
            data:{},
            traditional:false,
            success:null,
            fault:null,
            error:null,
            loading:false,
            redirect_url:null,
            dataType:"json",
            type:"get",
            cache:false,
            once:true
        };
        options= $.extend({},_default,options);
        options.url=options.url||location.href;
        if(options.once&&(options.url in inOperation))return false;
            var feedbackInfo;
            function success(response){
                if(options.dataType=="html"||options.dataType=="text"){
                    if(typeof(options.success)=='function')feedbackInfo=options.success(response);
                }else{
                    if(typeof(response)!="object"||response==""||!response){
                        response={errcode:-10000,errmsg:"服务器响应异常",data:''}
                    }
                    if(response.errcode==0){
                        if(typeof(options.success)=='function')feedbackInfo=options.success(response);
                    }else{
                        if(typeof(options.fault)=='function')feedbackInfo=options.fault(response);
                    }
                }
            }
            var uri=options.url+$.param(options.data);
            if(options.cache&&(uri in cache)){
                success(cache[uri]);
            }else{
                var loading=false,loadingSt;
                if(options.loading){
                    var loadingInfo={msg:"正在处理中....",timeout:500};
                    if(typeof(options.loading)=="object")$.extend(loadingInfo,options.loading);
                    loadingSt=setTimeout(function(){
                        loading=feedback.wait(loadingInfo.msg,60);
                    },loadingInfo.timeout);
                }
                if(options.once)inOperation[options.url]=options.data;
                $.ajax({
                    url:options.url,
                    type:options.type,
                    dataType:options.dataType,
                    data:options.data,
                    traditional:options.traditional,
                    complete:function(response){
                        if(loading!==false)loading.close();
                        if(loadingSt)clearTimeout(loadingSt);
                        setTimeout(function(){
                            feedbackInfo=null;
                            delete inOperation[options.url];
                        },(feedbackInfo&&typeof(feedbackInfo)=="object")?2000:(typeof(feedbackInfo)=="number"?feedbackInfo:0));
                        if(typeof(options.complete)=="function")options.complete(response);
                    },
                    success:function(response){
                        if(options.cache){cache[uri]=response;}
                        success(response);
                    },
                    error:function(e){
                        var response={errcode:-10001,errmsg:"服务器响应异常",data:e}
                        if(typeof(options.error)=="function")feedbackInfo=options.error(response);
                    }
                })
        }
        return true;
    }
    function postJump(url,param,target){
        target=target||"_self";
        var _form = $("<form></form>",{'method':'post','action':url,'target':target,'style':'display:none'}).appendTo($("body"));
        for(var key in param){
            if(typeof(param[key])=="object"&&("length" in param[key])){
                for(var i=0;i<param[key].length;i++){
                    _form.append($("<input>",{'type':'hidden','name':key+"[]",'value':param[key][i]}));
                }
            }else{
                _form.append($("<input>",{'type':'hidden','name':key,'value':param[key]}));
            }
        }
        _form.submit().remove();
    }

    exports.postJump=postJump;
    exports.post=post;
    exports.get=get;
})