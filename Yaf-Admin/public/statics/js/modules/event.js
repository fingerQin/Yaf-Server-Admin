define('Event',function(require,exports,module){
    function extendEventHandler(){
        var namespace = "$$";
        var currentTargets = {};
        var prototype=typeof(this)=="function"?this.prototype:this;
        prototype.addEventListener = function(eventType, handler) {
            eventType = namespace + eventType;
            var objects = currentTargets[eventType];
            if(objects != null) {
                for(var i = 0, len = objects.length; i < len; i++) {
                    var o = objects[i];
                    if(o["target"] === this && o["handler"] == handler) {
                        return;
                    }
                }
            }else{
                objects = currentTargets[eventType] = [];
            }
            objects.push({"target":this, "handler":handler});
        }
        prototype.removeEventListener = function(eventType, handler) {
            eventType = namespace + eventType;
            var objects = currentTargets[eventType];
            if(objects != null) {
                for(var i = 0, len = objects.length; i < len; i++) {
                    var o = objects[i];
                    if(o["target"] === this && o["handler"] == handler) {
                        objects.splice(i, 1);
                        if(len == 1) delete currentTargets[eventType];
                        return;
                    }
                }
            }
        }
        prototype.hasEventListener = function(eventType) {
            eventType = namespace + eventType;
            var objects = currentTargets[eventType];
            if(objects != null) {
                for(var i = 0, len = objects.length; i < len; i++) {
                    var o = objects[i];
                    if(o["target"] === this) {
                        return true;
                    }
                }
            }
        }
        prototype.dispatchEvent = function(event) {
            if(event && event.hasOwnProperty("type")) {
                var eventType = namespace + event["type"];
                var objects = currentTargets[eventType];
                if(objects != null) {
                    for(var i = 0, len = objects.length; i < len; i++) {
                        var o = objects[i];
                        if(o["target"] === this) {
                            var evtObj = {};
                            for(var k in event) {
                                evtObj[k] = event[k];
                            }
                            evtObj["target"] = this;
                            o["handler"](evtObj);
                        }
                    }
                }
            }
        }
    }
    exports.extend=extendEventHandler;
})