define('Popup',function(require,exports,module){
    var Cover=require('Cover');
    var template='<div class="popup"><div class="head"><a class="i-30 i-close"></a><h4></h4></div><div class="content"></div><div class="ctrl"><a class="sure">确定</a><a class="cancel">取消</a></div></div>';
    function popup(options){
        var _this = this;
        var _default = {
            title:'新建对话框',
            content:"正在加载...",
            init:null,
            closeType:'close',
            position:'fixed',
            ctrlBar:true,
            sureFunc:null,
            cancelFunc:null
        };
        options= $.extend({},_default,options);
        var $popup=$(template);
        var closeMethod = options.closeType=='hide'?'hide':'close';
        var sureFunc = options.sureFunc,cancelFunc = options.cancelFunc;
        $popup.find('.head h4').text(options.title);
        $popup.find('.content').html(options.content);
        _this.setTitle=function(title){
            $popup.find('.head h4').text(title);
            _this.adjust();
            return _this;
        };
        _this.setContent=function(content){
            $popup.find('.content').html(content);
            _this.adjust();
            return _this;
        };
        _this.setSureFunc = function(callback){
            sureFunc = callback;
            return _this;
        };
        _this.setCancelFunc = function(callback){
            cancelFunc = callback;
            return _this;
        };
        var cover=Cover.cover.call(this,{
            name:"popup",
            html:$popup,
            dragHandler:$popup.find('.head'),
            mask:true,
            position:options.position,
            init:options.init
        });
        $popup.find('.head .i-close').click(function(){
            if(typeof(cancelFunc)=='function'){
                if(cancelFunc.call(_this,$popup)!==false){
                    cover[closeMethod]();
                }
            }else{
                cover[closeMethod]();
            }
        });
        if(!options.ctrlBar){
            $popup.find('.ctrl').hide();
        }else {
            $popup.find('.sure').click(function(){
                if(typeof(sureFunc)=='function'){
                    if(sureFunc.call(_this,$popup)!==false){
                        cover[closeMethod]();
                    }
                }else{
                    cover[closeMethod]();
                }
            });
            $popup.find('.cancel,.head .i-close').click(function(){
                if(typeof(cancelFunc)=='function'){
                    if(cancelFunc.call(_this,$popup)!==false){
                        cover[closeMethod]();
                    }
                }else{
                    cover[closeMethod]();
                }
            });
        }
    }
    module.exports=popup;
});