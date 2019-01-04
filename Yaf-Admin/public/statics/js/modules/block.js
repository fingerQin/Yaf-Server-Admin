define("Block",function(require,exports,module){
    var http=require("Http");
    function block(options){
        var _this=this;
        var _default={
            url:"",
            type:"post",
            container:null,
            param:{},
            dataType:"html",
            method:"html",
            tpl:"",
            init:null,
            fault:null,
            loading:false,
            autoLoad:true,
            remember:false
        };
        options= $.extend({},_default,options);
        var $container=$(options.container);
        var $tpl=options.tpl;
        var $param=options.param;
        var $type=options.type=="get"?"get":"post";
        function query(param,callback,isExtend){
            if(typeof(callback)=="boolean")isExtend=callback;
            if(typeof(isExtend)=="undefined")isExtend=true;
            isExtend?$.extend($param,param):$param=param;
            refresh(callback);
        }
        function refresh(callback){
            http[$type]({
                url:options.url,
                data:$param,
                dataType:options.dataType,
                success:function(data){
                    if(options.remember)location.hash= $.param($param);
                    if(options.dataType=="json"){
                        $container[options.method](typeof($tpl)=="function"?$tpl(data.data):$tpl);
                    }else {
                        $container[options.method](data);
                    }
                    $container.triggerHandler({type:"refresh",response:data,param:$param});
                    if(typeof(callback)=="function")callback(data);
                },
                fault:function(data){
                    $container.triggerHandler({type:"refresh",response:{status:"fault",data: data},param:$param});
                },
                error:function(e){
                    $container.triggerHandler({type:"refresh",response:{status:"error",error: e.toString()},param:$param});
                },
                loading:options.loading
            })
        }
        function setTpl(tpl){
            $tpl=tpl;
        }
        function getTpl(){
            return $tpl;
        }
        function getParam(key){
            if(typeof(key)=="undefined"){
                return $param;
            }else{
                return $param[key]||"";
            }
        }
        function setContainer(container){
            $container=$(container);
        }
        function getContainer(container){
            return $container;
        }

        this.query=query;
        this.refresh=refresh;
        this.setTpl=setTpl;
        this.getTpl=getTpl;
        this.getParam=getParam;
        this.setContainer=setContainer;
        this.getContainer=getContainer;

        !function(){
            options.autoLoad?refresh(options.init):typeof(options.init)=="function"?options.init.call(_this):null;
        }();
    }
    module.exports=block;
});