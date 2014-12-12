Mohawk.Template = new Singletone ({
    vars: {},
    keywords: ['end', 'else'],

    assign: function (name, value) {
        if (Array.find(self.keywords, name) !== false) {
            alert(name + ' is a keyword');
        }
        self.vars[name] = value;
    },

    transform: function (text) {
        var init = '';
        foreach(self.vars, function (name) {
            init += 'var ' + name + ' = self.vars["' + name + '"];';
        });
        
        var variable = '[a-z_][a-z_0-9]*(?:\\[[^\\]]*\\]|\\.[a-z_][a-z_0-9]*)*';
        var modifier = '[a-z_][a-z_0-9]*(?:\\.[a-z_][a-z_0-9]*)*';
        var condition = '[^\\}]+';

        // pre-formating
        text = text.replace(new RegExp('\"', 'g'), '\\\"');
        text = text.replace(new RegExp('\\n', 'g'), '\\n');

        // loops
        text = text.replace(new RegExp('\\{%for\\s+(' + variable + ') in (' + variable + ')\\}', 'gmi'), '"; for (var __k__ in $2) {' + 'if (typeof Object.prototype[__k__] != \'undefined\' || typeof Array.prototype[__k__] != \'undefined\') continue; var $1 = $2[__k__]; __s__ += "');
        text = text.replace(new RegExp('\\{%for\\s+(' + variable + ') in (' + variable + ')\\s+=>\\s+(' + variable + ')\\}', 'gmi'), '"; for (var $3 in $2) {' + 'if (typeof Object.prototype[$3] != \'undefined\' || typeof Array.prototype[$3] != \'undefined\') continue; var $1 = $2[$3]; __s__ += "');

        // conditions
        text = text.replace(new RegExp('\\{%if\\s+(' + condition + ')\\}', 'gmi'), '"; if ($1) {__s__ += "');
        text = text.replace(new RegExp('\\{%elseif\\s+(' + condition + ')\\}', 'gmi'), '"; } else if ($1) {__s__ += "');
        text = text.replace(new RegExp('\\{%else\\}', 'gmi'), '"; } else { __s__ += "');
        
        // closing
        text = text.replace(new RegExp('\\{%end\\}', 'gmi'), '"; } __s__ += "');
        
        // replace variables    
        text = text.replace(new RegExp('\\{%(' + variable + ')\\|(' + modifier + ')\\}', 'gmi'), '" + $2($1) + "');
        text = text.replace(new RegExp('\\{%(' + variable + ')\\}', 'gmi'), '" + $1 + "');
        
        // uncomment for debugging:
        //Console.log('<plaintext>' + init + '__s__ = "' + text + '"' + '</plaintext>');
        
        // transformation
        eval(init + '__s__ = "' + text + '"');
        
        return __s__;
    }
    
});