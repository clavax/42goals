include('mohawk.kernel.Effects');
include('mohawk.kernel.Dragdrop');

Mohawk.UI.Console = new Class ({
    element: null,
    content: null,
    hidden: true,
    
    __construct: function () {
        var element = DOM.element('div');
        element.addClass('console');
        self.element = element;
        
        var content = document.createElement('UL');
        element.appendChild(content);
        element.collapse();
        self.content = content;
        
        var btn_clear = document.createElement('A');
        btn_clear.href = '#clear';
        btn_clear.innerHTML = 'clear';
        btn_clear.object = self;
        btn_clear.addClass('clear');
        btn_clear.onclick = function () {
            this.object.clear();
            return false;
        };
        self.element.appendChild(btn_clear);
        
        var btn_hide = document.createElement('A');
        btn_hide.href = '#hide';
        btn_hide.innerHTML = 'hide';
        btn_hide.object = self;
        btn_hide.addClass('hide');
        btn_hide.onclick = function () {
            this.object.hide();
            return false;
        };
        self.element.appendChild(btn_hide);

        var form = document.createElement('FORM');
        form.action = '#';
        form.history = [];
        form.current = 0;

        form.onsubmit = function () {
        	return false;
        };
        form.onclick = function () {
        	form.elements[0].focus();
        };
        self.element.appendChild(form);

    	var input = document.createElement('INPUT');
        input.history = [];
        input.current = 0;
        
    	input.onkeydown = function (event) {
    		event = DOM.event(event);
    		switch (event.key()) {
    		case 38: // up
    			if (this.current > 0) {
    				this.current --;
        			this.value = this.history[this.current];
    			}
    			break;
    		case 40: // down
    			if (this.current < this.history.length - 1) {
    				this.current ++;
        			this.value = this.history[this.current];
    			}
    			break;
    		case 13: // enter
    			try {
	            	var val = eval(this.value);
	            	self.log(val);
    			} catch (e) {
    				self.log('Error occured: ' + self.hash(e), 'error');
    			}
            	this.history.push(this.value);
            	this.current = this.history.length;
            	this.value = '';
            	return false;
    		}
    	};
    	input.onfocus = function () {
    		input.addClass('onfocus');
    	};
    	input.onblur = function () {
    		input.removeClass('onfocus');
    	};
    	form.appendChild(input);
    	self.input = input;
    },
    
    append: function () {
        document.body.appendChild(self.element);
    },
    
    log: function (text, type) {
        var now = new Date;
        var line = document.createElement('LI');
        line.innerHTML = IE ? '<pre>' + text + '</pre>' : text;
        if (type) {
            line.addClass(type);
        }

        var date = document.createElement('SPAN');
        date.innerHTML = now.toLocaleString();
        line.appendChild(date);
        
        self.content.appendChild(line);
        self.content.scrollTop = self.content.scrollHeight;
    },
    
    log2: function (title, text) {
        var line = self.content.lastChild ? self.content.lastChild : document.createElement('LI');
        var p = document.createElement('P');
        var toggle = document.createElement('A');
        var container = document.createElement('DIV');

        p.innerHTML = title + ': ';

        toggle.innerHTML = '+';
        toggle.href = '#toggle';
        toggle.onclick = function () {
            if (toggle.innerHTML == '+') {
                container.display();
                toggle.innerHTML = '-';
            } else {
                container.collapse();
                toggle.innerHTML = '+';
            }
            return false;
        };
        container.collapse();
        container.innerHTML = text;
        
        line.appendChild(p);
        p.appendChild(toggle);
        line.appendChild(container);
        
        self.content.appendChild(line);
        self.content.scrollTop = self.content.scrollHeight;
    },
    
    error: function (text) {
        self.log(text, 'error');
    },
    
    clear: function () {
        self.content.removeChildren();
    },
    
    hide: function () {
        self.hidden = true;
        self.element.collapse();
    },
    
    show: function () {
        self.hidden = false;
        self.element.display();
        self.element.scrollTop = self.element.scrollHeight;
        self.input.focus();
        Mohawk.Dragdrop.bringToFront(self.element);
    },
    
    toggle: function () {
        if (self.hidden) {
            self.show();
        } else {
            self.hide();
        }
    },
    
    hash: function (data) {
        var hash = '';
        switch (typeof data) {
        case 'object':
            if (data == null) {
                hash += 'NULL';
            } else {
                if (data.forEach) {
                    foreach(data, function (i) {
                        hash += '\n' + i + ':' + this;
                    });
                } else {
                    for (var i in data) {
                        hash += '\n' + i + ':' + data[i];
                    };
                }
            }
            break;
        case 'function':
            hash += '\n' + i + '()';
            break;
        case 'string':
            hash += '"' + data + '"';
            break;
        case 'number':
            hash += data;
            break;
        case 'boolean':
            hash += data ? 'TRUE' : 'FALSE';
            break;
        case 'undefined':
            hash += 'UNDEFINED';
            break;
        default:
            hash += typeof data;
        }
        return hash;
    },
    
    describe: function (data) {
        self.log(self.hash(data));
    },
    
    regKey: function () {
        document.console = self;
        document.addEvent('keydown', function (event) {
            event = DOM.event(event);
            if (event.ctrlKey) {
                switch (event.key()) {
                case 96:
                    if (!OPERA) {
                        break;
                    }
                case 192: // backquote
                    document.console.toggle();
                    break;
                }
            }
        });
    },
    
    handleErrors: function (flag) {
    	try {
	        if (flag == 'undefined') {
	            flag = true;
	        }
	        if (flag) {
	            if (!window.Console) {
	                window.Console = self;
	            }
	            window._onerror = window.onerror;
	            window.onerror = function (message, url, line) {
	                window.Console.error(message + ' (<a href="' + url + '">' + url + '</a> #' + line + ')');
	                return false;
	            }
	        } else {
	            if (window._onerror instanceof Function) {
	                window.onerror = window._onerror;
	            }
	        }
    	} catch (e) {
    		
    	}
    }
});