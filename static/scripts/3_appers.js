var appers = {
    solutions : {},
    patterns : {},
    libs : {},
    solutions_scripts : {}
};

var $$ = appers;

appers.getVar = (function(__variables){
    var variables = jQuery.extend(true, {}, __variables);
    return function(name) {
        return variables[name];
    };
})((typeof __appersVariables == 'undefined') ? [] : __appersVariables);

appers.loadScript = function (src, callback) {
    var s = document.createElement('script');
    s.type = 'text/' + (src.type || 'javascript');
    s.src = src.src || src;
    s.async = false;

    if(typeof callback == 'function') {
        var done = false;
        s.onreadystatechange = s.onload = function () {
            var state = s.readyState;
            if (!done && (!state || /loaded|complete/.test(state))) {
                done = true;
                callback();
            }
        };
    }

    // use body if available. more safe in IE
    (document.body || document.getElementsByTagName('HEAD')[0]).appendChild(s);
};

appers.isArray = function( variable ) {
    return Object.prototype.toString.call( variable ) === '[object Array]';
};

appers.serialize = function(obj) {
    var str = [];
    for(var p in obj)
    if (obj.hasOwnProperty(p)) {
        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
    }
    return str.join("&");
};

appers.serializeObject = function(obj, prefix) {
    var str = [];
    for(var p in obj) {
        var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
        str.push(typeof v == "object" ?
            appers.serialize(v, k) :
        encodeURIComponent(k) + "=" + encodeURIComponent(v));
    }
    return str.join("&");
};

appers.executeFunctionByName = function(functionName, args) {
    if(!appers.isArray(args)) {
        args = [args];
    }
    var namespaces = functionName.split(".");
    var func = namespaces.pop();
    context = window;
    for(var i = 0; i < namespaces.length; i++) {
        context = context[namespaces[i]];
    }
    return context[func].apply(context, args);
};
