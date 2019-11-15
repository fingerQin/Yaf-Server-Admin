define("Cookie",function(require,exports,module){
    function getCookie(cookiename) {
        var result;
        var mycookie = document.cookie;
        var start2 = mycookie.indexOf(cookiename + "=");
        if (start2 > -1) {
            start = mycookie.indexOf("=", start2) + 1;
            var end = mycookie.indexOf(";", start);
            if (end == -1) {
                end = mycookie.length;
            }
            result = unescape(mycookie.substring(start, end));
        }
        return result;
    }
    function setCookie(cookiename, cookievalue, hours) {
        var date = new Date();
        date.setTime(date.getTime() + Number(hours) * 3600 * 1000);
        document.cookie = cookiename + "=" + cookievalue + "; path=/;expires = " + date.toGMTString();
    }
    exports.getCookie=getCookie;
    exports.setCookie=setCookie;
})