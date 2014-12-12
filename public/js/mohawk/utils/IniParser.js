Mohawk.Utils.IniParser = new Class({
    parse: function (source) {
        source = source + '\n';
        
        var pos = 0;
        var data = {};
        var buffer = '';
        var status = static.STAT_NL;
        var key = '';
        
        while (pos < source.length) {
            var cur = source.slice(pos, pos + 1);

            switch (status) {
            
            case static.STAT_NL:
                if (cur == ';') {
                    status = static.STAT_COM;
                } else if (cur.match(new RegExp('\\w'))) {
                    status = static.STAT_KEY;
                    buffer += cur;
                } else if (cur.match(new RegExp('\\s'))) {
                    // white space or line break
                } else {
                    status = static.STAT_ERR;
                    buffer = '';
                }
                break;
                
            case static.STAT_COM:
            case static.STAT_ERR:
                if (cur.match(new RegExp('\\r\\n|\\n|\\r'))) {
                    status = static.STAT_NL;
                } else {
                    // continue comment
                }
                break;
                
            case static.STAT_KEY:
                if (cur.match(new RegExp('\\w'))) {
                    buffer += cur;
                } else if (cur == ' ' || cur == '	') {
                    status = static.STAT_END_KEY;
                    key = buffer;
                    buffer = '';
                } else if (cur == '=') {
                    status = static.STAT_VAL;
                    key = buffer;
                    buffer = '';
                } else {
                    status = static.STAT_ERR;
                    key = '';
                    buffer = '';
                }
                break;
                
            case static.STAT_END_KEY:
                if (cur == '=') {
                    status = static.STAT_VAL;
                } else if (cur == ' ' || cur == '	') {
                    // white space
                } else {
                    status = static.STAT_ERR;
                    key = '';
                }
                break;
                
            case static.STAT_VAL:
                if (cur == ' ') {
                    if (buffer) {
                        buffer += cur;
                        // status = static.STAT_END_VAL;
                    } else {
                        // do nothing
                    }
                } else if (cur.match(new RegExp('\\r\\n|\\n|\\r'))) {
                    status = static.STAT_NL; 
                    if (buffer) {
                        data[key] = buffer.rtrim();
                        buffer = '';
                    }
                    key = '';
                } else if (cur == '"') {
                    status = static.STAT_STR;
                } else if (cur == ';') {
                    status = static.STAT_COM;
                    if (buffer) {
                        data[key] = buffer;
                        buffer = '';
                    }
                    key = '';
                } else {
                    buffer += cur;
                }
                break;

            case static.STAT_STR:
                if (cur == '"') {
                    status = static.STAT_END_VAL;
                    data[key] = buffer;
                } else if (cur == '\\') {
                    status = static.STAT_ESC;
                } else {
                    buffer += cur;
                }
                break;

            case static.STAT_ESC:
                status = static.STAT_STR;
                if (cur.match(new RegExp('["\\\\]'))) {
                    buffer += cur;
                } else {
                    buffer += '\\' + cur;
                }
                break;

            case static.STAT_END_VAL:
                if (cur.match(new RegExp('\\r\\n|\\n|\\r'))) {
                    status = static.STAT_NL;
                    data[key] = buffer;
                    buffer = '';
                    key = '';
                } else if (cur == ';') {
                    status = static.STAT_COM;
                    data[key] = buffer;
                    buffer = '';
                    key = '';
                } else if (cur == ' ' || cur == '	') {
                    // white space
                } else {
                    status = static.STAT_ERR;
                    buffer = '';
                    key = '';
                }
                break;
                
            default:
                self.error('Unknown status = ' + status);
            }
            
            if (typeof(Console) !== 'undefined') {
                Console.log([cur, status, pos]);
            }
            pos ++;
        }
        
        return data;
    },
    
    error: function (str) {
//        if (Console) {
//            Console.log(str, 'error');
//        } else {
            alert(str);
//        }
    }
});

Mohawk.Utils.IniParser.STAT_KEY     = 'key';
Mohawk.Utils.IniParser.STAT_VAL     = 'value';
Mohawk.Utils.IniParser.STAT_END_KEY = 'endkey';
Mohawk.Utils.IniParser.STAT_END_VAL = 'endvalue';
Mohawk.Utils.IniParser.STAT_STR     = 'string';
Mohawk.Utils.IniParser.STAT_ESC     = 'escape';
Mohawk.Utils.IniParser.STAT_COM     = 'comment';
Mohawk.Utils.IniParser.STAT_ERR     = 'error';
Mohawk.Utils.IniParser.STAT_NL      = 'newline';
