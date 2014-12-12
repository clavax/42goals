Mohawk.Dragdrop = new Singletone ({
    css_class: 'draggable',
    max_index: 1000,
    dragged: false,
	caller: null,
    target: {
        elements: [],
        x_boxes: {},
        x_delim: [],
        y_boxes: {},
        y_delim: []
    },
    over: [],
    events: [],

    reset: function () {
        self.target = {
            elements: [],
            x_boxes: {},
            x_delim: [],
            y_boxes: {},
            y_delim: []
        };
        self.over = [];
        self.events = [];
        self.dragged = false;
        self.caller = null;
    },

    dragging: function () {
        return !!self.events.length;
    },
    
    binSearch: function (array, value) {
        var l = 0;
        var r = array.length - 1;
        if (array[l] >= value) {
            return l;
        }
        if (array[r] < value) {
            return r + 1;
        }
        do {
            var m = Math.round((r + l) / 2);
            if (m == l || m == r || array[m] == value) {
                break;
            } else if (array[m] < value) {
                l = m;
            } else {
                r = m;
            }
        } while (true);
        return m;
    },

    createTargetObjectElement: function (target, element, i, last) {
        self = Mohawk.Dragdrop;
        
        var n = target.elements.length;
        var box = Rect.fromNode(element[i]);
        
        // x axis
        var start_x = self.binSearch(target.x_delim, box.start.x);
        if (typeof target.x_boxes[box.start.x] == 'undefined') {
            target.x_boxes[box.start.x] = typeof target.x_delim[start_x - 1] != 'undefined' && typeof target.x_boxes[target.x_delim[start_x - 1]] != 'undefined' ? target.x_boxes[target.x_delim[start_x - 1]].concat([]) : [];
            target.x_delim.splice(start_x, 0, box.start.x);
        }
        
        var end_x = self.binSearch(target.x_delim, box.end.x);
        if (typeof target.x_boxes[box.end.x] == 'undefined') {
            target.x_boxes[box.end.x] = typeof target.x_boxes[target.x_delim[end_x - 1]] != 'undefined' ? target.x_boxes[target.x_delim[end_x - 1]].concat([]) : [];
            target.x_delim.splice(end_x, 0, box.end.x);
        }

        for (var j = start_x; j < end_x; j ++) {
            if (typeof target.x_boxes[target.x_delim[j]] == 'undefined') {
                target.x_boxes[target.x_delim[j]] = [];
            }
            target.x_boxes[target.x_delim[j]].push(n);
        }
        
        // y axis
        var start_y = self.binSearch(target.y_delim, box.start.y);
        if (typeof target.y_boxes[box.start.y] == 'undefined') {
            target.y_boxes[box.start.y] = typeof target.y_delim[start_y - 1] != 'undefined' && typeof target.y_boxes[target.y_delim[start_y - 1]] != 'undefined' ? target.y_boxes[target.y_delim[start_y - 1]].concat([]) : [];
            target.y_delim.splice(start_y, 0, box.start.y);
        }
        
        var end_y = self.binSearch(target.y_delim, box.end.y);
        if (typeof target.y_boxes[box.end.y] == 'undefined') {
            target.y_boxes[box.end.y] = typeof target.y_boxes[target.y_delim[end_y - 1]] != 'undefined' ? target.y_boxes[target.y_delim[end_y - 1]].concat([]) : [];
            target.y_delim.splice(end_y, 0, box.end.y);
        }

        for (var j = start_y; j < end_y; j ++) {
            if (typeof target.y_boxes[target.y_delim[j]] == 'undefined') {
                target.y_boxes[target.y_delim[j]] = [];
            }
            target.y_boxes[target.y_delim[j]].push(n);
        }

        var actions = ['ondragover', 'ondragout', 'ondrop'];
        for (var k = 0; k < actions.length; k ++) {
            var action = element[i][actions[k]] || element[i].getAttribute(actions[k]);
            try {
                element[i][actions[k]] = action instanceof Function ? new Function (['event'], action.parse().body) : false;
            } catch (e) {
                
            }
        }

        target.elements[n] = element[i];

        if (++ i < element.length) {
            setTimeout(function () {
                self.createTargetObjectElement(target, element, i, last);
            }, 0);
        } else if (last) {
//            Console.log('Create target object: ' + T.stop() + ' ms.');
        }
    },

    createTargetObject: function (element) {
        var target = {
            elements: [],
            x_boxes: {},
            x_delim: [],
            y_boxes: {},
            y_delim: []
        };

        // debugging:
//        T = new Mohawk.Utils.Timer;

        for (var n = 0; n < arguments.length; n ++) {
            self.createTargetObjectElement(target, arguments[n], 0, n == arguments.length - 1);
        }
        
        return target;
    },

    setTarget: function (element) {
        self.target = self.createTargetObject.call(self, arguments);
    },
    
    setTargetObject: function (object) {
        self.target = object;
    },
    
    getDraggable: function (node) {
        if (node.hasClass(self.css_class)) {
            return node;
        } else {
            if (node.parentNode) {
                return self.getDraggable(node.parentNode);
            } else {
                return null;
            }
        }
    },


    pick: function (event, elements) {
        self = Mohawk.Dragdrop;

        event = DOM.event(event);
        
        var caller = event.currentTarget || event.srcElement;
        self.caller = caller;
        if (!elements || !elements.length) {
            elements = [caller];
        }

        document.addEvent('mousemove', self.drag);
        document.addEvent('mouseup', self.release);

        if (IE) {
            document.body.focus();
        }
        document.addEvent('selectstart', self.returnFalse);
        caller.addEvent('dragstart', self.returnFalse);
        document.addEvent('dragstart', self.returnFalse);
        document.body.onselectstart = self.returnFalse;
        
        
        self.events = [];

        var cur = event.cursor();

        //var T = new Mohawk.Utils.Timer;
        foreach(elements, function (i) {
            var action = this.onpick || this.getAttribute('onpick');
            if (action instanceof Function) {
                var handler = new Function (['event'], action.parse().body);
                handler.call(this, event);
            }
            
            var draggable = self.getDraggable(this);
            if (draggable) {
                var evt = {
                    element: this,
                    draggable: draggable,
                    position: draggable.style.position || 'static', // TODO: empty style bug
                    offset: new Pixel(draggable.offsetLeft, draggable.offsetTop),
                    start: new Pixel(cur.x, cur.y),
                    ondrag: handler instanceof Function ? handler : false
                };
                
                var actions = ['ondrag', 'ondrop', 'onrelease'];
                for (var k = 0; k < actions.length; k ++) {
                    var action = this[actions[k]] || this.getAttribute(actions[k]);
                    evt[actions[k]] = action instanceof Function ? new Function (['event'], action.parse().body) : false;
                }
                
                self.events.push(evt);
                
                draggable.style.position = 'absolute';
                if (draggable.dragFront !== false) {
                    self.bringToFront(draggable);
                }
            }
        });
        //debug('Set elements in: ' + T.stop() + ' ms');
        event.stopPropagation();
        
        return false;
    },

    drag: function (event) {
        self = Mohawk.Dragdrop;

        event = DOM.event(event);
        
        if (!self.dragged) {
            document.removeEvent('mouseup', self.release);
            document.addEvent('mouseup', self.drop);
        }
        self.dragged = true;

        var cur = event.cursor();

        for (var i = 0; i < self.events.length; i ++) {
            var evt = self.events[i];
            var new_pix = new Pixel(evt.offset.x + cur.x - evt.start.x, evt.offset.y + cur.y - evt.start.y);

            var move = true;
            if (evt.ondrag) {
                if (evt.ondrag.call(evt.element, event, new_pix) === false) {
                	move = false;
                }
            }

            if (move) {
            	if (evt.draggable.dragX !== false) {
            		evt.draggable.style.left = new_pix.x + 'px';
            	}
            	if (evt.draggable.dragY !== false) {
            		evt.draggable.style.top  = new_pix.y + 'px';
            	}
            }
        }

        if (self.target.elements.length) {
            var x_boxes = self.target.x_boxes[self.target.x_delim[self.binSearch(self.target.x_delim, cur.x) - 1]];
            var y_boxes = self.target.y_boxes[self.target.y_delim[self.binSearch(self.target.y_delim, cur.y) - 1]];
            if (x_boxes && y_boxes) {
                var boxes = Array.intersect(x_boxes, y_boxes, true);
                for (var z = 0; z < boxes.length; z ++) {
                    if (cur.inside(Rect.fromNode(self.target.elements[boxes[z]]))) {
                        if (Array.find(self.over, self.target.elements[boxes[z]]) === false) {
                            self.over.push(self.target.elements[boxes[z]]);
                        }
                        if (self.target.elements[boxes[z]].ondragover) {
                            self.target.elements[boxes[z]].ondragover.call(self.target.elements[boxes[z]], event);
                        }
                    }
                }
            }
        }
        
        if (self.over.length) {
            for (var i = 0; i < self.over.length; i ++) {
                var over = self.over[i];
                var bound = Rect.fromNode(over);
                if (!cur.inside(bound)) {
                    if (over.ondragout) {
                        over.ondragout.call(over, event);
                    }
                    Array.remove(self.over, over);
                }
            }
        }
        
        return false;
    },

    drop: function (event) {
        self = Mohawk.Dragdrop;

        event = Mohawk.DOM.event(event);

        document.removeEvent('mousemove', self.drag);
        document.removeEvent('mouseup', self.drop);
        
        self.caller.removeEvent('dragstart', self.returnFalse);
        document.removeEvent('selectstart', self.returnFalse);
        document.removeEvent('dragstart', self.returnFalse);
        document.body.onselectstart = null;

        var cur = event.cursor();
        if (self.over.length) {
            for (var i = 0; i < self.over.length; i ++) {
                var over = self.over[i];
                var bound = Rect.fromNode(over);
                if (cur.inside(bound)) {
                    if (over.ondrop) {
                        over.ondrop.call(over, event);
                    }
                }
            }
        }

        for (var i = 0; i < self.events.length; i ++) {
            var evt = self.events[i];
            if (evt.ondrop) {
                evt.element.ondrop.call(evt.element, event);
            }
        }
        self.reset();
    },

    release: function (event) {
        self = Mohawk.Dragdrop;

        event = Mohawk.DOM.event(event);

        document.removeEvent('mousemove', self.drag);
        document.removeEvent('mouseup', self.release);
        
        self.caller.removeEvent('dragstart', self.returnFalse);
        document.removeEvent('selectstart', self.returnFalse);
        document.removeEvent('dragstart', self.returnFalse);
        document.body.onselectstart = null;

        for (var i = 0; i < self.events.length; i ++) {
            var evt = self.events[i];
            if (evt.element.onrelease) {
                evt.element.onrelease.call(evt.element, event);
            }
        }
        self.reset();
    },

    bringToFront: function (element) {
        element.style.zIndex = self.max_index;
        self.max_index ++;
    },

    getDragObjects: function () {
        var objects = [];
        for (var i = 0; i < self.events.length; i ++) {
            objects.push(self.events[i].draggable);
        }
        return objects;
    },
    
    returnFalse: function () {
    	return false;
    }

});

window.Dragdrop = Mohawk.Dragdrop;