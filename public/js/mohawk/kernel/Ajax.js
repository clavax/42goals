include('mohawk.kernel.Forms');
include('mohawk.kernel.XML');

Mohawk.Ajax = new Class({
    XHR: null,
    caller: null,
    url: '',
    method: '',
    overwrite_method: true,
    data: '',
    charset: 'UTF-8',
    sync: true,
    xml: '',
    text: '',
    type: '',
    data: {},

    __construct: function (url, method) {
        self.url = url;
        self.method = method || static.METHOD_GET;
        self.type = static.TYPE_XML;
        self.XHR = Mohawk.XHR();
    },

    responseHandler: function () {

    },

    errorHandler: function () {

    },

    send: function (data) {
        if (typeof self.XHR == 'object') {
            self.XHR.onreadystatechange = function () {
            	self.procedure.apply(self);
            };
            
            self.method = self.method.toUpperCase()
            
            // initialize
            switch (self.method) {
            default:
            case static.METHOD_GET:
                var url_with_data = self.url + (self.url.match(new RegExp('\\?')) ? '&' : '?') + static.prepare(data);
                self.XHR.open(static.METHOD_GET, url_with_data, self.sync);
                var get = true;
                break;

            case static.METHOD_POST:
            case static.METHOD_PUT:
            case static.METHOD_DELETE:
            case static.METHOD_OPTIONS:
            case static.METHOD_HEAD:
                self.XHR.open(self.overwrite_method ? static.METHOD_POST : self.method, self.url, self.sync);
                var get = false;
                break;
            }
            
            // set headers
            self.XHR.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=' + self.charset);
            self.XHR.setRequestHeader('X-Request-Method', self.method);
            self.XHR.setRequestHeader('Accept', self.type);
            
            // send query
            if (get) {
                if (window.XMLHttpRequest) {
                    self.XHR.send(null);
                } else {
                    self.XHR.send();
                }
            } else {
                var query = static.prepare(data);
                self.XHR.send(query);
            }
            
            if (window.Console) {
            	var hash = window.Console.hash(data);
            	hash = hash.replace(new RegExp('<', 'g'), '&lt;');
            	hash = hash.replace(new RegExp('>', 'g'), '&gt;');
            	var report = 'Ajax query : ' + self.method + ' <a href="' + self.url + '">' + self.url + '</a><br />'
                           + 'Data: <pre>' + hash + '</pre>'
                           + 'Query: ' + query;
            	window.Console.log(report);
            }
            
        } else {
            self.error('XMLHttpRequest is not initialized');
        }
    },

    procedure: function () {
        var error = false;
        
        switch (self.XHR.readyState) {

        case static.STATE_UNINITIALIZED:
            break;

        case static.STATE_LOADED:
            if (self.XHR.status == static.HTTP_OK) {
                if (self.XHR.responseXML) {
                    self.xml = self.XHR.responseXML;
                } else {
                    if (self.type == static.TYPE_XML && self.errorHandler instanceof Function) {
                        error = true;
                    	self.errorHandler.call(self);
                    }
                }
            } else {
                if (self.errorHandler instanceof Function) {
                    error = true;
                	self.errorHandler(self);
                }
            }
            self.text = self.XHR.responseText;
            if (window.Console) {
            	var data = self.XHR.responseText;
            	data = data.replace(new RegExp('<', 'g'), '&lt;');
            	data = data.replace(new RegExp('>', 'g'), '&gt;');
            	var report = 'Ajax query received from: <a href="' + self.url + '">' + self.url + '</a>';
//                           + 'Data: <pre>' + data + '</pre>';
            	window.Console.log(report);
            	window.Console.log2('Data', data);
            }
            
            switch (self.type) {
            case static.TYPE_JSON:
                eval('self.data = ' + self.text); 
                break;
            case static.TYPE_XML:
                self.data = Mohawk.XML.toObject(self.xml);
                break;
            }
            if (!error) {
                self.responseHandler(self);
            }
            break;
            
        default:
            // something is wrong here
        }
    },
    
    error: function (message) {
        if (window.Console) {
            window.Console.log(message, 'error');
        }
    }
});

// static variables and methods
Mohawk.Ajax.HTTP_OK = 200;
Mohawk.Ajax.HTTP_NOT_FOUND = 404;

Mohawk.Ajax.STATE_UNINITIALIZED = 0;
Mohawk.Ajax.STATE_OPEN          = 1;
Mohawk.Ajax.STATE_SENT          = 2;
Mohawk.Ajax.STATE_RECEIVING     = 3;
Mohawk.Ajax.STATE_LOADED        = 4;

Mohawk.Ajax.METHOD_GET     = 'GET';
Mohawk.Ajax.METHOD_POST    = 'POST';
Mohawk.Ajax.METHOD_PUT     = 'PUT';
Mohawk.Ajax.METHOD_DELETE  = 'DELETE';
Mohawk.Ajax.METHOD_HEAD    = 'HEAD';
Mohawk.Ajax.METHOD_OPTIONS = 'OPTIONS';

Mohawk.Ajax.TYPE_XML  = 'text/xml';
Mohawk.Ajax.TYPE_JSON = 'text/json';
Mohawk.Ajax.TYPE_TEXT = 'text/text';

Mohawk.Ajax.escape = function (str) {
    if (typeof str == 'string') {
        if (OPERA) {
            // to avoid spare linebreaks in opera
            str = str.replace(new RegExp('\r', 'g'), '');
        }
        str = str.replace(new RegExp('%', 'g'), '%' + '25');
        str = str.replace(new RegExp('\n', 'g'), '%' + '0A');
        str = str.replace(new RegExp('\r', 'g'), '%' + '0D');
        str = str.replace(new RegExp('&', 'g'), '%' + '26');
        str = str.replace(new RegExp('#', 'g'), '%' + '23');
        str = str.replace(new RegExp('\\+', 'g'), '%' + '2B');
    }
    return str;
};

Mohawk.Ajax.prepare = function (data, prefix) {
    var query = '';
    switch (typeof data) {
    case 'object':
        if (data instanceof Array) {
            for (var i = 0; i < data.length; i ++) {
                if (typeof Array.prototype[i] != 'undefined') {
                    continue;
                }
                query += (query.length ? '&' : '');
                switch (typeof data[i]) {
                case 'object':
                    query += Mohawk.Ajax.prepare(data[i], prefix ? prefix + '[' + i + ']' : i);
                    break;
    
                case 'string':
                case 'number':
                case 'boolean':
                    query += (prefix ? prefix + (!prefix.match(new RegExp('\\[\\]$')) ? '[]' : '') + '=' : i + '=') + Mohawk.Ajax.prepare(data[i], prefix ? prefix + '[' + i + ']' : i);
                    break;
                }
            }
        } else {
            for (var i in data) {
                if (typeof Object.prototype[i] != 'undefined') {
                    continue;
                }
                query += (query.length ? '&' : '');
                switch (typeof data[i]) {
                case 'object':
                    if (data[i] == null) {
                        query += prefix ? prefix + (!prefix.match(new RegExp('\\[\\]$')) ? '[' + i + ']' : '') + '=' : i + '='; 
                    } else {
                        query += Mohawk.Ajax.prepare(data[i], prefix ? prefix + (!prefix.match(new RegExp('\\[\\]$')) ? '[' + i + ']' : '') : i);
                    }
                    break;
    
                case 'string':
                case 'number':
                case 'boolean':
                    query += (prefix ? prefix + (!prefix.match(new RegExp('\\[\\]$')) ? '[' + i + ']' : '') + '=' : i + '=') + Mohawk.Ajax.prepare(data[i], prefix ? prefix + '[' + i + ']' : i);
                    break;
                }
            }
        }
        break;

    case 'string':
    case 'number':
    case 'boolean':
        query = prefix ? Mohawk.Ajax.escape(data) : data;
        break;
    }
    return query;
};

Mohawk.Ajax.parse_url = function (url) {
    var chunks = url.split('&');
    var data = {};
    foreach(chunks, function () {
        var tuple = this.split('=');
        var m;
        if (m = tuple[0].match(new RegExp('([a-z][a-z_0-9]*)((?:\\[[a-z_0-9]*\\])*)', 'i'))) {
            var rec = data;
            var last = m[1].toString();
            if (m[2]) {
                var keys = m[2].slice(1, -1).split('][');
                keys.unshift(m[1]);
                last = keys.pop();
                var prev_rec, prev_key;
                foreach(keys, function () {
                    var key = this.toString();
                    if (!(rec[key] instanceof Object)) {
                        rec[key] = {};
                    }
                    prev_rec = rec;
                    prev_key = key;
                    rec = rec[key];
                });
            }
            if (last == '') {
                if (!(rec instanceof Array)) {
                    prev_rec[prev_key] = [];
                }
                prev_rec[prev_key].push(tuple[1]);
            } else {
                rec[last] = tuple[1];
            }
        }
    });
    return data;
};

window.Ajax = Mohawk.Ajax;