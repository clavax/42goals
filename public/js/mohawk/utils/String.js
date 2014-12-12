var StringInterface = {
    trim: function () {
        return this.rtrim().ltrim();
    },
    
    ltrim: function() {
        return this.replace(new RegExp('^\\s+'), '');
    },
    
    rtrim: function() {
        return this.replace(new RegExp('\\s+$'), '');
    },
    
    pad: function (length, chr, right) {
        var str = this;
        var size = length - this.length;
        chr = chr || ' ';
        for (var i = 0; i < size; i ++) {
            str = right ? str.concat(chr) : chr.concat(str); 
        }
        return str;
    },
    
    ucfirst: function () {
        if (!this.length) {
            return '';
        }
        var first = this.slice(0, 1);
        return first.toUpperCase() + this.slice(1, this.length);
    },
    
    htmlspecialchars: function () {
        var str = this.valueOf();
        str = str.replace(/&/g, '&amp;');
        str = str.replace(/</g, '&lt;');
        str = str.replace(/>/g, '&gt;');
        return str;
    },
    
    nl2br: function () {
        return this.replace(/\r\n|\n\r|\n|\r/g, '<br />');
    },
    
    wordwrap: function (width, str) {
        var len = 0;
        var wrapped = '';
        if (str == undefined) {
            str = '\n';
        }
        for (var i = 0; i < this.length; i ++) {
            var char = this.substr(i, 1);
            if (char.match(/\s/)) {
                len = 0;
            } else {
                len ++;
            }
            if (len > width) {
                wrapped = wrapped.concat(str);
                len = 1;
            }
            wrapped = wrapped.concat(char);
        }
        return wrapped;
    }
};
extend(String, StringInterface);