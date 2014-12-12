Mohawk.Utils.Cookie = new Singletone ({
    STORAGE_DOCUMENT_COOKIE: 'document.cookie',
    STORAGE_WINDOW_NAME: 'window.name',
    
    storage: null,
    
    __construct: function () {
        self.storage = self.STORAGE_DOCUMENT_COOKIE;
    },
    
    set: function (name, value, expires, path, domain, secure) {
        var cookie = name + '=' + escape(Ajax.prepare(value));
        if (expires) {
            cookie += '; max-age=' + expires;
        }
        cookie += '; path=' + (path || '/');
        if (domain) {
            cookie += '; domain=' + domain;
        }
        if (secure) {
            cookie += '; secure';
        }

        // write cookie
        switch (self.storage) {
        case self.STORAGE_DOCUMENT_COOKIE:
            document.cookie = cookie;
            break;
        case self.STORAGE_WINDOW_NAME:
            top.name = cookie;
            break;
        }
    },
    
    get: function (name) {
        var cookie = '';
        
        // read cookie
        switch (self.storage) {
        case self.STORAGE_DOCUMENT_COOKIE:
            cookie = document.cookie;
            break;
        case self.STORAGE_WINDOW_NAME:
            cookie = top.name;
            break;
        }
        
        var tuples = cookie.split(';');
        var value = null;
        for (var i = 0; i < tuples.length; i ++) {
            var tuple = tuples[i].toString().trim().split('=');
            if (tuple[0].toString().trim() == name) {
                value = Ajax.parse_url('data=' + unescape(tuple[1]));
                break;
            }
        };
        
        return value == null || value.data == null ? null : value.data;
    }
});