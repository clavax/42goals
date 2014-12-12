Mohawk.EventInterface = {
    preventDefault: function () {
        // Cross browser function to prevent the default action from occurring.
        if (this.button == BTN_RIGHT) {
            if (OPERA) {
                // small trick to make opera happy with context menu
                // adds input button under mouse pointer
                no_ctmenu = window.no_ctmenu;
                if (!no_ctmenu) {
                    no_ctmenu = document.createElement('input');
                    no_ctmenu.type='button';
                    document.body.appendChild(no_ctmenu);
                }
                no_ctmenu.style.position = 'fixed';
                no_ctmenu.style.top = this.clientY - 2 + 'px';
                no_ctmenu.style.left = this.clientX - 2 + 'px';
                no_ctmenu.style.width = '5px';
                no_ctmenu.style.height = '5px';
                no_ctmenu.style.opacity = 0;
                no_ctmenu.style.zIndex = 10000;
            }
        }
        if (typeof this.returnValue != 'undefined') {
            this.returnValue = false;
        } else {
            if (window._preventDefault instanceof Function) {
                window._preventDefault.call(this);
            }
        }

        return false;
    },

    stopPropagation: function () {
        // Cross browser function to prevent the event from bubbling.
        if (typeof this.cancelBubble != 'undefined') {
            this.cancelBubble = true;
        } else {
            if (window._stopPropagation instanceof Function) {
                window._stopPropagation.call(this);
            }
        }
        return false;
    },

    cursor: function () {
        var x = 0, y = 0;

        if (this.pageX || this.pageY) {
            x = this.pageX;
            y = this.pageY;
        } else if (this.clientX || this.clientY) {
            x = this.clientX + document.scrollLeft();
            y = this.clientY + document.scrollTop();
        } else if (this.screenX || this.screenY) {
            x = this.screenX;
            y = this.screenY;
        }

        return new Pixel(x, y);
    },
    
    wheel: function () {
        var delta = 0;
        if (this.wheelDelta) {
            delta = event.wheelDelta / 120;
//            if (OPERA) {
//                delta = -delta;
//            }
        } else if (this.detail) {
            delta = -this.detail / 3;
        }
        return delta;
    },
    
    key: function () {
        return this.keyCode || this.which || null;
    },

    element: function () {
        return this.srcElement || this.currentTarget;
    }
};

if (window.Event) {
    window._preventDefault = window.Event.prototype.preventDefault;
    window._stopPropagation = window.Event.prototype.stopPropagation;
    if (FF) {
        delete(Mohawk.EventInterface.preventDefault);
    }
    extend(window.Event, Mohawk.EventInterface);
}

Mohawk.NodeClassInterface = {
    //return all classes as an array
    getClasses: function() {
        return this.className ? this.className.split(/\s+/) : [];
    },

    //checks class
    hasClass: function(className) {
        var classes = this.getClasses();
        for (var i = 0; i < arguments.length; i ++) {
            if (Array.find(classes, arguments[i]) !== false) {
                return true;
            }
        }
        return false;
    },

    //add class
    addClass: function(className) {
        if (!this.className.length) {
            this.className = className;
        } else if (!this.hasClass(className)) {
            this.className += ' ' + className;
        }
        for (var i = 1; i < arguments.length; i ++) {
            if (!this.hasClass(arguments[i])) {
                this.className += ' ' + arguments[i];
            }
        }
    },

    //remove class
    removeClass: function(className) {
        var classes = this.getClasses();
        for (var i = 0; i < arguments.length; i ++) {
            Array.remove(classes, arguments[i]);
        }
        this.className = classes.join(' ');
    },

    //replace class
    replaceClass: function(find, replace) {
        var classes = this.getClasses();
        if ((key = Array.find(classes, find)) !== false) {
            classes[key] = replace;
            this.className = classes.join(' ');
        } else {
            this.addClass(replace);
        }
    },

    //set class if not isset, otherwise remove it
    flipClass: function(className) {
        for (var i = 0; i < arguments.length; i ++) {
            if (this.hasClass(arguments[i])) {
                this.removeClass(arguments[i]);
            } else {
                this.addClass(arguments[i]);
            }
        }
    },

    copyClassesTo: function (element) {
        var classes = this.getClasses();
        classes.forEach (function () {
            element.addClass(this);
        });
    },
    
    setClassesTo: function (element) {
        element.className = this.className;
    }
};

Mohawk.NodeEventInterface =  {
    addEvent: function (event, action, useCapture) {
        if (!useCapture) {
            useCapture = false;
        }
        if (this.addEventListener) {
            this.addEventListener(event, action, useCapture);
        } else if (this.attachEvent) {
            this.attachEvent('on' + event, action);
        }

        return action;
    },

    removeEvent: function (event, action, useCapture) {
        if (!useCapture) {
            useCapture = false;
        }
        if (this.removeEventListener) {
            this.removeEventListener(event, action, useCapture);
        } else if (this.detachEvent) {
            this.detachEvent('on' + event, action);
        }
    }
};

Mohawk.NodeStructureInterface = {
    coordinates: function () {
        var x = 0, y = 0;
        var element = this;
        while (element) {
            x += element.offsetLeft;
            y += element.offsetTop;
            element = element.offsetParent;
        }
        return new Pixel(x, y);
    },

    isDescendantOf: function (node) {
        var parent = this.parentNode;
        while (parent) {
            if (parent == node) {
                return true;
            }
            parent = parent.parentNode;
        }
        return false;
    },

    isAncestorOf: function (node) {
        var parent = node.parentNode;
        while (parent) {
            if (parent == this) {
                return true;
            }
            parent = parent.parentNode;
        }
        return false;
    },

    removeChildren: function () {
        while (this.firstChild) {
            this.removeChild(this.firstChild);
        }
    },

    firstTag: function (tag) {
        if (typeof tag == 'undefined') {
            tag = false;
        }
        var current = this.firstChild;
        while (current && (!current.tagName || (tag && current.tagName.toLowerCase() != tag.toLowerCase()))) {
            current = current.nextSibling;
        }
        return current;
    },

    nextTag: function (tag) {
        if (typeof tag == 'undefined') {
            tag = false;
        }
        var current = this.nextSibling;
        while (current && (!current.tagName || (tag && current.tagName.toLowerCase() != tag.toLowerCase()))) {
            current = current.nextSibling;
        }
        return current;
    },

    ancestorTag: function (tag) {
        if (typeof tag == 'undefined') {
            tag = false;
        }
        var current = this.parentNode;
        while (current && (!current.tagName || (tag && current.tagName.toLowerCase() != tag.toLowerCase()))) {
            current = current.parentNode;
        }
        return current;
    },

    previousTag: function (tag) {
        if (typeof tag == 'undefined') {
            tag = false;
        }
        var current = this.previousSibling;
        while (current && (!current.tagName || (tag && current.tagName.toLowerCase() != tag.toLowerCase()))) {
            current = current.previousSibling;
        }
        return current;
    },

    replace: function (node) {
        this.parentNode.replaceChild(node, this);
    },
    
    insertFirst: function(node) {
        this.insertBefore(node, this.firstChild);
    },
    
    insertAfter: function (node, before) {
        this.insertBefore(node, before.nextSibling);
    },
    
    getElementsByClassName: function (class_name) {
        var elements = this.getElementsByTagName('*');
        var found = [];
        for (var i = 0; i < elements.length; i ++) {
            if (elements[i].hasClass && elements[i].hasClass(class_name)) {
                found.push(elements[i]);
            }
        }
        return found;
    },
    
    getElementsByLang: function (lang) {
        var elements = this.getElementsByTagName('*');
        var found = [];
        for (var i = 0; i < elements.length; i ++) {
            if (elements[i].lang == lang) {
                found.push(elements[i]);
            }
        }
        return found;
    },
    
    setHTML: function (html) {
        this.purgeChildren();
        this.innerHTML = html;
        Mohawk.DOM.enchaseDocumentNodes(this);
    },
    
    prependHTML: function (html) {
    	this.innerHTML = html + this.innerHTML;
    	Mohawk.DOM.enchaseDocumentNodes(this);
    },

	appendHTML: function (html) {
		this.innerHTML += html;
		Mohawk.DOM.enchaseDocumentNodes(this);
	},
	
	purge: function () {
	    this.parentNode.purgeChild(this);
	},
	
	purgeChild: function (node) {
        var a = node.attributes, i, l, n;
        if (a) {
            l = a.length;
            for (i = 0; i < l; i += 1) {
                n = a[i].name;
                if (node[n] instanceof Function) {
                    node[n] = null;
                }
            }
        }
        if (node.purgeChildren instanceof Function) {
            node.purgeChildren();
        }
        this.removeChild(node);
	    node = null;
	},
	
	purgeChildren: function () {
	    while (this.firstChild) {
            this.purgeChild(this.firstChild);
        }
    },
	
	setChild: function (node) {
        this.purgeChildren();
        this.appendChild(node);
    },
    
    appendTo: function (node) {
        node.appendChild(this);
    }
};

Mohawk.ElementOnlyInterface = {
    getElementById: function (id) {
        var elements = this.getElementsByTagName('*');
        var found = false;
        for (var i = 0; i < elements.length; i ++) {
            if (elements[i].id == id) {
                found = elements[i];
                break;
            }
        }
        return found;
    }
};

Mohawk.NodePositionInterface = {
    alignTo: function (node, side, absolute) {
        switch (side) {
        case 'left':
            this.style.left = (absolute ? node.coordinates().x : node.offsetLeft) + 'px';
            break;

        case 'right':
            this.style.left = (absolute ? node.coordinates().x : node.offsetLeft) + node.offsetWidth - this.offsetWidth + 'px';
            break;
            
        case 'center':
            this.style.left = (absolute ? node.coordinates().x : node.offsetLeft) + (node.offsetWidth - this.offsetWidth) / 2 + 'px';
            break;
            
        case 'top':
            this.style.top = (absolute ? node.coordinates().y : node.offsetTop) + 'px';
            break;
            
        case 'bottom':
            this.style.bottom = (absolute ? node.coordinates().y : node.offsetTop) + node.offsetHeight + 'px';
            break;

        case 'middle':
            this.style.top = (absolute ? node.coordinates().y : node.offsetTop) + (node.offsetHeight - this.offsetHeight) / 2 + 'px';
            break;
        }
    },
    
    adjoinTo: function (node, side, absolute) {
        switch (side) {
        case 'left':
            this.style.right = (absolute ? node.coordinates().x : node.offsetLeft) + 'px';
            break;
            
        case 'right':
            this.style.left = (absolute ? node.coordinates().x : node.offsetLeft) + node.offsetWidth + 'px';
            break;
            
        case 'top':
            this.style.bottom = (absolute ? node.coordinates().y : node.offsetTop) + 'px';
            break;
            
        case 'bottom':
            this.style.top = (absolute ? node.coordinates().y : node.offsetTop) + node.offsetHeight + 'px';
            break;
        }
    }
};
    
Mohawk.DOM = new Singletone ({
    ELEMENT_NODE:        1,
    TEXT_NODE:           3,
    CDATA_SECTION_NODE : 4,
    DOCUMENT_NODE :      9,

    enchaseNode: function (node) {
        if (!node) {
            return;
        }
    
        if (IE && typeof(HTMLGenericElement) != 'undefined' && node instanceof HTMLGenericElement) {
            return;
        }

        if (Array.find([self.ELEMENT_NODE, self.DOCUMENT_NODE], node.nodeType) === false) {
			return;
		}
		
		if (node._enchased instanceof Function && node._enchased() === true) {
            return;
        }
		
		if (!node.nodeName || node.nodeName.match(/^(?:svg|OBJECT)/)) {
		    return;
		}
		
        node._cloneNode = node.cloneNode;
        node.cloneNode = function (flag) {
            var clone = this._cloneNode(flag);
            self.enchaseNode(clone);
            return clone;
        };

        switch (node.nodeName.toUpperCase()) {
        case 'TABLE':
            node._insertRow = node.insertRow;
            node.insertRow = function (index) {
                var row = IE ? this._insertRow(index) : HTMLTableElement.prototype.insertRow.call(this, index);
                self.enchaseNode(row);
                return row;
            };
            
            node._createTHead = node.createTHead;
            node.createTHead = function () {
                var thead = IE ? this._createTHead() : HTMLTableElement.prototype.createTHead.call(this);
                self.enchaseNode(thead);
                return thead;
            };
            
            node._createTFoot = node.createTFoot;
            node.createTFoot = function () {
                var tfoot = IE ? this._createTFoot() : HTMLTableElement.prototype.createTFoot.call(this);
                self.enchaseNode(tfoot);
                return tfoot;
            };
            
            break;
            
        case 'TR':
            node._insertCell = node.insertCell;
            node.insertCell = function (index) {
                var cell = IE ? this._insertCell(index) : HTMLTableRowElement.prototype.insertCell.call(this, index);
                self.enchaseNode(cell);
                return cell;
            };
            break;
            
        case 'FORM':
            if (typeof Mohawk.FormsInterface != 'undefined') {
                extend(node, Mohawk.FormsInterface);
            }
            break;
        }
        
        extend(node, Mohawk.NodeClassInterface);
        extend(node, Mohawk.NodeEventInterface);
        extend(node, Mohawk.NodeStructureInterface);
        extend(node, Mohawk.NodePositionInterface);
        if (node.nodeType == self.ELEMENT_NODE) {
            extend(node, Mohawk.ElementOnlyInterface);
        }
        
        if (Mohawk.NodeEffectInterface instanceof Object) {
            extend(node, Mohawk.NodeEffectInterface);
        }

        node._enchased = function () {
        	return true;
        };
    },

    enchaseDocument: function () {
        if (document._document_enchased) {
            return;
        }
        document._document_enchased = true;
        
        var createElement = document.createElement;
        document.createElement = function (tag_name) {
            if (typeof Document == 'undefined') {
                var element = createElement(tag_name);
            } else {
                var element = (Document.prototype.createElement || createElement).call(document, tag_name);
            }
            self.enchaseNode(element);
            return element;
        };

        var getElementById = document.getElementById;
        document.getElementById = function (id) {
            if (typeof Document == 'undefined') {
                var element = getElementById(id);
            } else {
                var element = (Document.prototype.getElementById || getElementById).call(document, id);
            }
            self.enchaseNode(element);
            return element;
        };

        document.scrollLeft = function () {
            return self.pageXOffset
                || (document.documentElement && document.documentElement.scrollLeft)
                || (document.body && document.body.scrollLeft);
        };
        document.scrollTop = function () {
            return self.pageYOffset
                || (document.documentElement && document.documentElement.scrollTop)
                || (document.body && document.body.scrollTop);
        };
        document.size = function () {
            var w = 0, h = 0;
            var win = window.window;
    
            if (win.self.innerHeight) {
                w = win.self.innerWidth;
                h = win.self.innerHeight;
            } else if (win.document.documentElement && win.document.documentElement.clientWidth) {
                w = win.document.documentElement.clientWidth;
                h = win.document.documentElement.clientHeight;
            } else if (win.document.body) {
                w = win.document.body.clientWidth;
                h = win.document.body.clientHeight;
            }
            return {width: w, height: h};
        };

        extend(document, Mohawk.NodeEventInterface);
        extend(document, Mohawk.NodeStructureInterface);
        extend(document, Mohawk.NodePositionInterface);
        
        document._toHideOnClick = [];
        document.addEvent('click', function () {
            foreach(document._toHideOnClick, function () {
                this.hide();
            });
        });

        if (typeof document.defaultView == 'undefined') {
            document.defaultView = {};
        }
        if (typeof document.defaultView.getComputedStyle == 'undefined') {
            document.defaultView.getComputedStyle = function (element, pseudoElement) {
                return element.currentStyle;
            };
        }
    },
    
    enchaseWindow: function () {
        if (window._window_enchased) {
            return;
        }
        window._window_enchased = true;
        extend(window, Mohawk.NodeEventInterface);
    },

    enchaseDocumentNodes: function (node) {
        if (node) {
            self.enchaseNode(node);
        }
        self.enchaseNode(document.body);
        var all = (node || document.body).getElementsByTagName('*');
        if (all.forEach instanceof Function) {
	        foreach(all, function () {
	        	self.enchaseNode(this);
	        });
        } else {
			for (var i = 0; i < all.length; i ++) {
				self.enchaseNode(all[i]);
			}
        }
    }
});

Mohawk.DOM.element = function (tag_name, args) {
    var element;
    if (tag_name.toUpperCase() == 'INPUT') {
        if (IE && !IE9) {
            var str = '<input';
            if (args) {
                if (args.name != undefined) {
                    str += ' name="' + args.name + '"';
                }
                if (args.type != undefined) {
                    str += ' type="' + args.type + '"';
                }
            }
            str += '>';
            element = document.createElement(str);
        } else {
            element = document.createElement('INPUT');
            if (args) {
                if (args.name != undefined) {
                    element.name = args.name;
                }
                if (args.type != undefined) {
                    element.type = args.type;
                }
            }
        }
    } else if (tag_name.toUpperCase() == 'BUTTON') {
        if (IE && !IE9) {
            var str = '<button';
            if (args) {
                if (args.name != undefined) {
                    str += ' name="' + args.name + '"';
                }
                if (args.type != undefined) {
                    str += ' type="' + args.type + '"';
                }
            }
            str += '>';
            element = document.createElement(str);
        } else {
            element = document.createElement('BUTTON');
            if (args) {
                if (args.name != undefined) {
                    element.name = args.name;
                }
                if (args.type != undefined) {
                    element.type = args.type;
                }
            }
        }
    } else {
        element = document.createElement(tag_name);
    }
    if (args && args instanceof Object) {
        delete(args['name']);
        delete(args['type']);
        foreach(args, function (arg, value) {
            if (arg == 'className') {
                if (value instanceof Array) {
                    element.addClass.apply(element, value);
                } else {
                    element.addClass(value);
                }
            } else if (arg == 'html') {
                element.setHTML(value);
            } else if (arg == 'appendTo') {
                element.appendTo(value);
            } else {
                element[arg] = value;
            }
        });
    }
    return element;
};

Mohawk.DOM.text = function (text) {
    return document.createTextNode(text);
};

Mohawk.DOM.event = function (event) {
    event = event || window.event;
    extend(event, Mohawk.EventInterface);
    return event;
};

Mohawk.DOM.stopEvent = function (event) {
    event = DOM.event(event);
    event.stopPropagation();
};

Mohawk.DOM.enchaseWindow();
Mohawk.DOM.enchaseDocument();

document.addLoader(
    function () {
        Mohawk.DOM.enchaseDocumentNodes();
    }
);

window.ID = function (id) {
    return document.getElementById(id);
};