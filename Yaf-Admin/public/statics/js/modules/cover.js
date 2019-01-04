define("Cover",function (require, exports, module) {
    var htmlTpl=['<div class="cover_outer"></div>','<div class="cover_box"></div>'];
    var store=[],keyStore={};
    function cover(options){
       var _default={
           html:"正在加载...",
           init:null,
           position:"fixed",
           offset:{left:0,top:0,origin:"body"},
           dragHandler:null,
           mask:true,
           name:"cover",
           maskVisible:true
       };
       options= $.extend(true,{},_default,options);
       var _this=this;
       var $cover=$(!options.mask?htmlTpl[1]:htmlTpl.join("")),$box=$cover.filter(".cover_box"),$outer=$cover.filter(".cover_outer");
       if(!options.maskVisible)$outer.addClass("cover_outer_mask","");
       $box.html(options.html);
       $("body").append($cover);
       adjust();
       if(options.position=="relative")$(window).resize(adjust);
       function hide(){
           $cover.hide();
           return _this;
       }
       function show(){
           $cover.show();
           return _this;
       }
       function close(){
           store = $.grep(store,function(item){
               if(item===_this){
                   delete keyStore[options.id];
                   $cover.remove();
                   return false;
               }
               return true;
           });
       }
       function adjust(){
           if(options.position=="relative"){
               var $origin=$(options.offset.origin);
               var left=$origin.offset().left,top=$origin.offset().top;
               left+=options.offset.left,top+=options.offset.top;
               $box.css({left:left,top:top,"position":"absolute"});
               return false;
           }else{
               $box.css({width:"auto",height:"auto"});
               var width=$box.width(),height=$box.height(),scrollTop=$(window).scrollTop();
               var left=-width/ 2,top=-height/2;
               $box.css({marginLeft:left,marginTop:top,left:"50%",top:"50%",width:width,height:height});
               if(options.position=="absolute")$box.css({"position":"absolute",marginTop:top+scrollTop});
           }
           return _this;
       }

        function dragFun(handler){
            !handler.hasClass("dragHandle")?handler.addClass("dragHandle"):false;
            var o,c;
            function draggingFun(event){
                var n={x:event.pageX,y:event.pageY};
                $box.css({
                    "margin-left": (c.x+n.x-o.x)+"px",
                    "margin-top":(c.y+n.y-o.y)+"px"
                });
            }
            handler.mousedown(function(event){
                //coverOut.find("iframe").hide();
                o={x:event.pageX,y:event.pageY},c={x:parseInt($box.css("margin-left").replace("px","")),y:parseInt($box.css("margin-top").replace("px",""))};
                $(document).bind("mousemove",draggingFun);
            });
            $(document).mouseup(function(){
                $(this).unbind("mousemove",draggingFun);
                //coverOut.find("iframe").show();
            });
        }

        this.getContent=function(){return $box;}
        this.adjust=adjust;
        this.close=close;
        this.hide=hide;
        this.show=show;

       if(options.name)this.name=options.name;
       if(options.id)this.id=options.id;

        if(typeof(options.init)=="function")options.init.call(this,$box,$outer);
        if(typeof(options.dragHandler)!=null&&$box.find(options.dragHandler).length>0){
            var handler=$box.find(options.dragHandler);
            dragFun(handler);
        }
        store.push(this);
        if(options.id)keyStore[options.id]=this;

       return this;
    }
    function show(options){
        return new cover(options).show();
    }
    function clear(){
        for(var i=0;i<store.length;i++){
            store[i].close();
        }
        store=[];
    }
    function clearByName(name){
        for(var i=0;i<store.length;i++){
            if(store[i].name==name)store[i].close();
        }
    }
    function getStore(){
        return store;
    }
    function getStoreByName(name){
        var result=[];
        for(var i=0;i<store.length;i++){
            if(store[i].name==name)result.push(store[i]);
        }
        return result;
    }
    function getStoreById(id){
        return keyStore[id];
    }
    function adjust(){
        for(var i=0;i<store.length;i++){
            store[i].adjust();
        }
    }
    exports.cover=cover;
    exports.clear=clear;
    exports.adjust=adjust;
    exports.clearByName=clearByName;
    exports.getStoreByName=getStoreByName;
    exports.getStoreById=getStoreById;
    exports.getStore=getStore;
    exports.show=show;
});