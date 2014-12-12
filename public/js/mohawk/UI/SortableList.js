include('mohawk.kernel.Dragdrop');
include('mohawk.UI.OrderedList');

Mohawk.UI.SortableList = Mohawk.UI.OrderedList.extend ({
    node_margin: new Pixel(0, 0),
    marker_size: {width: 16, height: 16},
    marker_class: 'marker',
    orientation: 'vertical',
    target: null,
    
    __construct: function (id, data) {
        parent.__construct(id, data);
        
        self.EVENT_SORTED = self.id + '-sorted';
        self.EVENT_MOVED  = self.id + '-moved';
                
        self.element.ondragover = function (event) {
            event = DOM.event(event);
            var marker = this.object.findMarker(event);
            if (marker === false) {
                this.object.removeMarker();
            } else {
                if (marker.after === true) {
                    this.object.setMarkerAfter(marker.node);
                } else {
                    this.object.setMarkerBefore(marker.node);
                }
            }
        };
        
        self.element.ondragout = function (event) {
            this.object.removeMarker();
        };
        
        self.element.ondrop = function (event) {
            var self = this.object;
            var node = Dragdrop.events[0].element.proto;
            var moved = node.parentNode != self.element;
            var marker = self.findMarker(event);
            if (marker) {
                if (marker.after === true) {
                    self.element.insertBefore(node, marker.node.nextSibling);
                } else {
                    self.element.insertBefore(node, marker.node);
                }
                if (moved) {
                    Observer.fire(self.EVENT_MOVED, node, self.element);
                } else {
                    Observer.fire(self.EVENT_SORTED, self.element.childNodes);
                }
            }
            self.removeMarker();
            return marker;
        };
    },
    
    createNode: function (data) {
        var node = parent.createNode(data);

        node.onmousedown = function (event) {
            event = DOM.event(event);
            if (event.button != BTN_LEFT) {
                return;
            }
            var clone = self.createClone(node);
            
            Dragdrop.setTarget(self.element);
            self.element.appendChild(clone);
            Dragdrop.pick(event, [clone]);
            return false;
        };
        
        return node;
    },
    
    createClone: function (node) {
        var clone = node.cloneNode(true);
        clone.addClass(Dragdrop.css_class);
        clone.ondrop = function () {
            this.remove();
        };
        clone.onrelease = function (event) {
            this.remove();
        };
        if (!self.movable) {
	        if (self.orientation == 'vertical') {
	        	clone.dragX = false;
	        } else {
	        	clone.dragY = false;
	        }
        }

        clone.style.position    = 'absolute';
        clone.style.left        = node.offsetLeft - self.node_margin.x + 'px';
        clone.style.top         = node.offsetTop - self.node_margin.y + 'px';
        clone.style.width       = node.offsetWidth + 'px';
        clone.style.height      = node.offsetHeight + 'px';
        
        clone.setOpacity(0.75);
        clone.cloned = true;
        clone.proto = node;
        return clone;
    },
    
    findMarker: function (event) {
        event = Mohawk.DOM.event(event);
        
        var marker = true;
        var last = false;
        var prev = false;
        var cur  = event.cursor();
        
        for (var i = 0; i < self.element.childNodes.length; i ++) {
            var child = self.element.childNodes[i];
            if (!child.cloned) {
                last = child;
                var coord = child.coordinates();
                
                if (self.orientation == 'vertical') {
	                if (cur.y > coord.y - child.offsetHeight / 2) {
	                    prev = child;
	                } else {
	                    return prev ? {node: prev, after: false} : false;
	                }
                } else {
	                if (cur.x > coord.x - child.offsetWidth / 2) {
	                    prev = child;
	                } else {
	                    return prev ? {node: prev, after: false} : false;
	                }
                }
            }
        }
        
        if (self.orientation == 'vertical') {
        	return {node: last, after: cur.y > last.coordinates().y};
        } else {
        	return {node: last, after: cur.x > last.coordinates().x};
        }
    },
    
    createMarker: function (x, y) {
        if (!self.marker) {
            var marker = document.createElement('DIV');
            marker.addClass(self.marker_class);
            marker.style.position = 'absolute';
            document.body.appendChild(marker);
            Dragdrop.bringToFront(marker);
            self.marker = marker;
        }
        self.marker.style.left = x + 'px';
        self.marker.style.top = y + 'px';
    },
    
    setMarkerBefore: function (node) {
        var x = node.coordinates().x - self.node_margin.x - self.marker_size.width / 2;
        var y = node.coordinates().y - self.marker_size.height / 2;
        self.createMarker(x, y);
    },
    
    setMarkerAfter: function (node) {
    	if (self.orientation == 'vertical') {
            var x = node.coordinates().x + self.node_margin.x - self.marker_size.width / 2;
            var y = node.coordinates().y + node.offsetHeight - self.marker_size.height / 2;
    	} else {
            var x = node.coordinates().x + self.node_margin.x + node.offsetWidth - self.marker_size.width / 2;
            var y = node.coordinates().y - self.marker_size.height / 2;
    	}
        self.createMarker(x, y);
    },
    
    removeMarker: function () {
        if (self.marker) {
            self.marker.remove();
            self.marker = null;
        }
    }
});