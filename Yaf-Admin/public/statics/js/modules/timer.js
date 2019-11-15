define("Timer",function(require,exports,module){
    var one_min= 60,one_hours=60*one_min,one_day=one_hours*24;
    function time2str(times){
        if(typeof(times)=="object"&&(times instanceof Array)){
            var timeStr=[];
            for(var i=0;i<times.length;i++){
                var numStr=times[i].toString();
                numStr=(numStr.length==1?["0",numStr].join(""):numStr);
                timeStr.push(numStr);
            }
            return timeStr.join("").split("");
        }else if(typeof(times)=="number"){
            return time2str(time2DHIS(times));
        }
    }
    function view(viewArr,timeStr){
        var html="";
        for(var i =0;i<viewArr.length;i++){
            html+=viewArr[i];
            if(i<timeStr.length)html+=typeof(timeStr)=="string"?timeStr.charAt(i):timeStr[i];
        }
        return html;
    }
    function parseCountDownTime(disTime,process){
        process(time2DHIS(disTime),disTime);
    }
    function time2DHIS (disTime){
        var tempTime=disTime;
        var dis_day=Math.floor(tempTime/one_day);
        tempTime-=dis_day*one_day;
        var dis_hours=Math.floor(tempTime/one_hours);
        tempTime-=dis_hours*one_hours;
        var dis_min=Math.floor(tempTime/one_min);
        tempTime-=dis_min*one_min;
        var dis_sec=tempTime;
        return [dis_day,dis_hours,dis_min,dis_sec];
    }
    function Countdown(options){
        var t=this;
        var _default={
            expire:Math.floor(new Date().getTime()/1000),
            now:Math.floor(new Date().getTime()/1000),
            container:null,
            hackTime:0,
            template:'<i>%_%</i><i>%_%</i><span class="cd_d">天</span><i>%_%</i><i>%_%</i><span class="cd_h">时</span><i>%_%</i><i>%_%</i><span class="cd_m">分</span><i>%_%</i><i>%_%</i><span class="cd_s">秒</span>'
        };
        options= $.extend(_default,options);
        var expireTime=options.expire,nowTime=options.now,hackTime=options.hackTime;
        var $container=$(options.container);
        function process(disTimeArr,disTime){
            if(typeof(options.process)=="function")options.process.apply(t,[disTimeArr,disTime]);
            if(typeof(options.template)=="string"){
                var themes=options.template.split("%_%");
                $container.html(view(themes,time2str(disTimeArr)));
            }else if(typeof(options.template)=="function"){
                $container.html(options.template(disTimeArr));
            }
        }
        function end(){
            if(typeof(options.end)=="function")options.end.apply(this);
        }
        function getServerTime(time){
            return time+hackTime;
        }
        function getNowTime(){
            return Math.floor(new Date().getTime()/1000)+hackTime;
        }
        this.getServerTime=getServerTime;
        this.getNowTime=getNowTime;

        function init(){
            var distime=expireTime-getNowTime();
            if(distime>=0){
                parseCountDownTime(distime,process);
                var interval=setInterval(function(){
                    distime=expireTime-getNowTime();
                    if(distime>0){
                        parseCountDownTime(distime,process);
                    }else{
                        clearInterval(interval);end();
                    }
                },1000);
            }else{
                end();
            }
        }
        if(options.serverTime){
            $.ajax({
                url:options.serverTime,
                success:function(data){
                    if(data.time){
                        hackTime=data.time-getNowTime();
                        init();
                    }
                }
            });
        }else{
            init();
        }
    }
    exports.countdown=function (options){
        return new Countdown(options);
    };
    exports.time2str=time2str;
    exports.view=view;
})