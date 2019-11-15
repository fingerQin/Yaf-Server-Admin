define("Feedback",function(require,exports,module){
    var coverUtil=require("Cover");
    var tpl={
        success:'<div class="feedback-success"><div class="box"><div class="head"><i></i></div><div class="message">{$message}<br /><a class="redirect" href="{$redirect}">立即跳转</a></div></div><div class="bbar"><span class="time">{$time}</span>秒后页面跳转</div>',
        message:'<div class="feedback-message"><div class="box"><i class="i-left"></i><i class="i_right"></i><div class="message {$className}">{$message}</div></div>',
        wait:'<div class="feedback-wait"><div class="box"><p align="center"><i class="ico"></i></p><p>{$message}<span class="dot"></span></p></div></div>'
    };
    function view(html, data) {
        data=data||"";
        html=html.replace(/\{\$(\w+)\}/g,function(i,m){
            return data[m]||"";
        })
        return html;
    }
    function createTemporaryCover(content,callback,time,hidMask,visibleMask){
        var ctime= 0;time=time?time:3;
        var feedback=coverUtil.show({
            name:"feedback",
            html:content,
            mask:hidMask?false:true,
            maskVisible:visibleMask?true:false
        }),$feedback=feedback.getContent(),$dot=$feedback.find(".dot"),$time=$feedback.find(".time");
        clearTimeout(st);
        var dots=[".","..","...","....",""];
        var st=setInterval(function(){
            if(time-ctime<=1){
                if(typeof(callback)=="function")callback.call(feedback);
                clearTimeout(st);return;
            }else{
                $dot.html(dots[ctime%5]);
                ctime++;
            }
            $time.html(time-ctime);
        },1000);
        return feedback;
    }
    function success(message,redirect,time){
        time=time||2;
        return createTemporaryCover(view(tpl.success,{message:message,time:time,redirect:redirect||location.href}),function(){
            if(typeof(redirect)=="function"){
                redirect.call(this);
            }else if(typeof(redirect)=="string"){
                redirect?location.href=redirect:location.reload();
            }
        },time)
    }
    var currentMessage;
    function message(message,time,className){
        time=time||2;
        if(currentMessage)currentMessage.close();
        currentMessage=createTemporaryCover(view(tpl.message,{message:message,className:className||""}),function(){
            this.close();
        },time,true);
        return currentMessage;
    }
    function wait(message,time){
        time=time||60;
        return createTemporaryCover(view(tpl.wait,{message:message}),null,time,false,false)
    }
    exports.success=success;
    exports.message=message;
    exports.wait=wait;
});