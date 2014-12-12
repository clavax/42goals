include('mohawk.utils.IniParser');
include('mohawk.utils.String');

Mohawk.Loader = new Singletone({    
    css: [],

    css_default_url: URL.css + 'mohawk/',

    addCss: function (url) {
        if (!url.match(new RegExp('^(http://|/)', 'i'))) {
            url = self.css_default_url + url;
        }
        if (document.loaded) {
        	self.importCss(url);
        } else {
	        if (Array.find(self.css, url) === false) {
	            self.css.push(url);
	        }
        }
    },

    loadCss: function () {
        foreach(self.css, function () {
            self.importCss(this);
        });
    },

    importCss: function (src) {
        var link = document.createElement('LINK');
        link.href = src;
        link.type = 'text/css';
        link.rel = 'stylesheet';
        document.getElementsByTagName('HEAD')[0].appendChild(link);
    },

    js: [],

    js_default_url: URL.home + 'system/js/',
    
    addJs: function (url) {
        url = url || '';
        
        if (!url.match(new RegExp('^(http://|/)', 'i'))) {
            url = Mohawk.js_url + url;
        }
        if (document.loaded) {
        	self.loadJs(url);
        } else {
	        if (Array.find(self.js, url) === false) {
	            self.js.push(url);
	        }
        }
    },
    
    loadJs: function () {
        foreach(self.js, self.importJs);
    },
    
    importJs: function (src) {
        var script = DOM.element('script', {
            src: src,
            type: 'text/javascript',
            appendTo: document.getElementsByTagName('HEAD')[0]
        });
    },
    
    extendLanguage: function (lng) {
        foreach(Object.keys(lng), function () {
            var key = this.toString();
            var str = lng[key].toString();
            
            lng[key.ucfirst()]     = str.ucfirst();
            lng[key.toUpperCase()] = str.toUpperCase();
            lng[key.toLowerCase()] = str.toLowerCase();
        });
        
        extend(window.LNG, lng);
    },

    includeLanguage: function (path) {
        var include_path = URL.lang + ENV.language + '/' + path + '.ini';
        
        if (Array.find(Mohawk.included, include_path) === false) {
            // create XHR
            var XHR = Mohawk.XHR();
            XHR.open('GET', include_path + '?' + (new Date).valueOf(), false);
            XHR.send(null);
            try {
                var parser = new Mohawk.Utils.IniParser();
                var lng = parser.parse(XHR.responseText);
                self.extendLanguage(lng);
            } catch (e) {
                if (Console) {
                    Console.describe(e);
                }
            }
            Mohawk.included.push(include_path);
            delete(XHR);
        }
    },

    includeTemplate: function (path) {
        var include_path = URL.html + path.replace(new RegExp('\\.', 'g'), '/') + '.tmpl';
        if (Array.find(Mohawk.included, include_path) === false) {
            // create XHR
            var XHR = Mohawk.XHR();
            XHR.open('GET', include_path + '?' + (new Date).valueOf(), false);
            XHR.send(null);
            
            var str = XHR.responseText;
            str = str.replace(new RegExp('"', 'g'), '\\"');
            // str = str.replace(new RegExp('/', 'g'), '\/');
            str = str.replace(new RegExp('([\n\r]+)', 'g'), '" + "\\n');
            str = '"' + str + '"';
            
            var name = path.substr(path.lastIndexOf('.') + 1).toUpperCase();
            name = name.replace(new RegExp('\\W', 'g'), '_');
            eval('window.' + name + ' = ' + str);

            Mohawk.included.push(include_path);
            delete(XHR);
        }
    }
});

window.LNG = {};

document.addLoader(
    function () {
    	Mohawk.Loader.loadCss();
    	Mohawk.Loader.loadJs();
    }
);