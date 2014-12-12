var Mohawk = {
    version: '0.1',
    UI: {
        selection: false,
        selectables: []
    },
    
    Utils: {
    },
    
    DOM: {
    },
    
    url: URL.js,
    
    XHR: function () {
    	var XHR = null;
        if (window.XMLHttpRequest) {
            XHR = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            XHR = new ActiveXObject('Microsoft.XMLHTTP');
        } else {
            alert('XMLHttpRequest is disabled in your browser');
            return false;
        }
    	
    	return XHR;
    }
};
    
var IE = typeof(window.ActiveXObject) != 'undefined';
var FF = navigator.userAgent.match('Gecko') != null;
var OPERA = typeof(window.opera) != 'undefined';
var WEBKIT = navigator.userAgent.match('WebKit') != null;
if (WEBKIT) {
    FF = false;
}

var BTN_LEFT = IE ? 1 : 0;
var BTN_MIDDLE = IE ? 4 : 1;
var BTN_RIGHT = 2;

function extend(target, source, directly) {
    if (target.prototype && !directly) {
        extend(target.prototype, source);
    } else {
    	/*if (typeof(target.base) == 'undefined') {
    		target.base = {};
    	}*/
        for (var i in source) {
        	/*if (typeof(target[i]) != 'undefined') {
        		if (typeof(target.base[i]) == 'undefined') {
        			target.base[i] = target[i];
        		}
        	}*/
            target[i] = source[i];
        }
    }
};

Function.prototype.parse = function () {
    var source = this.toSource instanceof Function ? this.toSource() : this.toString();
    // opera 9.5 does not work with '\\{(.*)\\}'
    // so instead we use \\{([^\x01]*)\\}
    // hope that \x01 character won't appear in code
    var regex = new RegExp('\\(?\\s*function\\s*([a-z$_][a-z$_0-9]*)?\\s*\\(\\s*([^\\)]*)\\s*\\)\\s*\\{([^\x01]*)\\}\\s*\\)?\\s*', 'img');
    var m = null;
    if (m = regex.exec(source)) {
        return {
            name: m[1],
            params: m[2].split(new RegExp('\\s*,\\s*')),
            body: m[3]
        };
    } else {
        return null;
    }
};

function foreach (iterable, action) {
    if (!action || !action.call) {
        return;
    }
    if (iterable instanceof Array || (typeof(iterable.length) == 'number' && (IE || typeof(iterable.item) == 'function'))) {
        for (var i = 0; i < iterable.length; i ++) {
            var value = action.call(iterable[i], i, iterable[i]);
            if (value === false) {
                break;
            }
        }
    } else if (iterable instanceof Object) {
        for (var i in iterable) {
            if (typeof(Object.prototype[i]) == 'undefined') {
                var value = action.call(iterable[i], i, iterable[i]);
                if (value === false) {
                    break;
                }
            }
        }        
    }
}

var ObjectInterface = {
    combine: function (keys, values) {
        var obj = {};
        for (var i = 0; i < keys.length; i ++) {
            obj[keys[i]] = values[i];
        }
        return obj;
    },

    find: function (object, value) {
        for (var i in object) {
            if (value == object[i]) {
                return i;
            }
        }
        return false;
    },

    keys: function (object) {
        var keys = [];
        for (var i in object) {
            if (typeof(Object.prototype[i]) == 'undefined') {
                keys.push(i);
            }
        }
        return keys;
    },

    values: function (object) {
        var values = [];
        foreach(object, function () {
            values.push(this);
        });
        return values;
    },

    clone: function(object) {
        var clone = {};
        foreach(object, function (key) {
            clone[key] = object[key];
        });
        return clone;
    }
};

var ArrayInterface = {
    remove: function (array, value) {
        var index = Array.find(array, value);
        if (index !== false) {
            array.splice(index, 1);
        }
    },
    
    find: function (array, value) {
        for (var i = 0; i < array.length; i ++) {
            if (value == array[i]) {
                return i;
            }
        }
        return false;
    },

    intersect: function (array1, array2, sorted) {
        if (!sorted) {
            array1.sort();
            array2.sort();
        }
        var i = 0, j = 0;
        var unique = [];
        while (i < array1.length && j < array2.length) {
            if (array1[i] < array2[j]) {
                i ++;
            } else if (array1[i] > array2[j]) {
                j ++;
            } else {
                unique.push(array1[i]);
                i ++;
                j ++;
            }
        }
        return unique;
    }
};

extend(Object, ObjectInterface, true);
extend(Array, ArrayInterface, true);

Math._round = Math.round;
var MathInterface = {
    sign: function (x) {
        return x == 0 ? 0 : (x > 0 ? 1 : -1);
    },

    rand: function (min, max) {
        if (!max) {
            min = 0;
            max = min;
        }
        return Math.round(Math.random() * (max - min)) + min;
    },
    
    round: function (num, precision) {
    	if (!precision || precision < 0) {
    		return Math._round(num);
    	} else {
    		var base = Math.pow(10, precision);
    		return Math._round(num * base) / base;
    	}
    }
};
extend(Math, MathInterface);

var NumberInterface = {
    toHex: function () {
        if (this == 0) {
             return '0';
        }
        var hex = '';
        var digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'];
        var d = this;
        var pow = Math.floor(Math.log(d) / Math.log(16));
        var base = Math.pow(16, pow);
        for (var i = pow; i >= 0; i --) {
            var q = Math.floor(d / base);
            d -= q * base;
            hex += digits[q];
            base /= 16;
        }
        return hex;
    }
};
extend(Number, NumberInterface);

RegExp.quote = function (str) {
    var regexp = new RegExp('([\\<\\>\\/\\?\\.\\$\\^\\[\\]\\*\\+\\\\])', 'g');
    return str.replace(regexp, '\\$1');
};

var Class = function (object) {
    return this.extend(object);
};

var Singletone = function (object) {
    var class_interface = new Class(object);
    var instance = new class_interface();
    return instance;
};

Class.prototype = {
    self: null,
    parent: null,
    extend: function (descendant) {
        var child = Class.method(descendant.__construct || (this.prototype && this.prototype.__construct ? this.prototype.__construct : function () {}), 'Class.setSelf(this); var self = this.self; var parent = this.parent; this.static = arguments.callee; var static = this.static; ');

        // copy parent prototype
        var methods = Object.keys(descendant);
        child.prototype.parent = {};
//        child.prototype.__base = child.prototype; // uncomment this line if you need base class

        for (var i in this.prototype) {
            if (this.prototype[i] instanceof Function) {
                child.prototype.parent[i] = Class.method(this.prototype[i]);
            } else {
                child.prototype.parent[i] = this.prototype[i];
            }

            if (i != 'parent') {
                if (this.prototype[i] instanceof Function) {
                    child.prototype[i] = Class.method(this.prototype[i], Array.find(methods, i) === false ? 'if (parent && parent.parent) {parent = parent.parent};' + Class.label : '');
                } else {
                    child.prototype[i] = this.prototype[i];
                }
            }
        }
        
        // copy static properties
    	if (typeof(child.base) == 'undefined') {
    		child.base = {};
    	}
        for (var i in this) {
        	if (typeof(child[i]) == 'undefined') {
                child[i] = this[i];
        	}
        }        

        // copy child prototype
        for (var i in descendant) {
            if (descendant[i] instanceof Function) {
                child.prototype[i] = Class.method(descendant[i], 'var __function__ = "' + i + '"; var self = this.self; var parent = this.parent; var static = this.static; ' + Class.label + 'if (self) {try {parent.self = self} catch (e) {}}');
            } else {
                child.prototype[i] = descendant[i];
            }
        }

        child.extend = this.extend;

        return child;
    }
};

Class.extend = Class.prototype.extend;
Class.label = 'Class.here();';
Class.here = function () {};
Class.method = function (action, add) {
    var source = action.parse();
    var f = function () {};
    if (source) {
        if (source.body.indexOf(Class.label) > 0) {
            source.body = source.body.replace(Class.label, add || '');
        } else {
            source.body = (add || '') + source.body;
        }
        
        var str = 'f = function (' + source.params.join(',') + ') {' + source.body + '};';
        
        //str = str.replace(new RegExp('\r\n', 'g'), '\n');
        //str = str.replace(new RegExp('\n', 'g'), '\r\n');
        eval(str);
    } else {
        // TODO: some error here
        f = false;
    }
    return f;
};
Class.setSelf = function (object) {
    var base = object;
    do {
        object.self = base;
        object = object.parent;
    } while (object && object.parent);
};

// onDOMContentLoaded simulation
document.loaders = [];

document.callLoaders = function () {
    if (document.loaded) {
    	while (document.loaders.length) {
    		var loader = document.loaders.shift();
            if (loader instanceof Function) {
                loader.call(document);
            }
    	}
    }
};

if (FF) {
    document.addEventListener('DOMContentLoaded',
        function () {
            document.loaded = true;
            document.callLoaders.call(document);
        }, false
    );
} else if (WEBKIT) {
    function callLoaders () {
        if (Array.find(['loaded', 'complete'], document.readyState) !== false) {
            document.loaded = true;
            document.callLoaders.call(document);
        } else {
            setTimeout(callLoaders, 0);
        }
    }
    setTimeout(callLoaders, 0);
} else {
    document.onreadystatechange = function () {
        if (Array.find(['loaded', 'complete'], document.readyState) !== false) {
            document.loaded = true;
            document.callLoaders();
        }
    };
}

document.addLoader = function (action) {
    if (document.loaded) {
        action.call(document);
    } else {
        if (Array.find(document.loaders, action) === false) {
            document.loaders.push(action);
        }
    }
};

Mohawk.included = [];
function include(path) {
	var first = path.substr(0, path.indexOf('.'));
	var url = Mohawk.url;
	if (typeof(URL[first]) != 'undefined') {
		url = URL[first];
		path = path.substr(path.indexOf('.') + 1);
	}
    var include_path = url + path.replace(new RegExp('\\.', 'g'), '/') + '.js';
    if (Array.find(Mohawk.included, include_path) === false) {
        // create XHR
    	var XHR = Mohawk.XHR();
        XHR.open('GET', include_path + '?' + (new Date).valueOf(), false);
        XHR.setRequestHeader('Pragma', 'no-cache');
        XHR.setRequestHeader('Expires', '-1');
        XHR.send(null);
        eval(XHR.responseText);
        Mohawk.included.push(include_path);
        delete(XHR);
    }
}

include('mohawk.kernel.geom');
include('mohawk.kernel.Template');
include('mohawk.kernel.Effects');
include('mohawk.kernel.DOM');
include('mohawk.kernel.Loader');
include('mohawk.kernel.Observer');

var DOM = Mohawk.DOM;
var Loader = Mohawk.Loader;
var Template = Mohawk.Template;
Template.assign('URL', URL);
var Effects = Mohawk.Effects;
var Observer = Mohawk.Observer;