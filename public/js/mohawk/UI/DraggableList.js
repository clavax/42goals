include('mohawk.kernel.Dragdrop');
include('mohawk.UI.SelectableList');

Mohawk.UI.DraggableList = Mohawk.UI.SelectableList.extend({
    node_margin: new Pixel(5, 5),
    marker_size: {width: 6, height: 42},
    marker: null,
    orientation: 'vertical',
    target: [],
    target_object: {},
    
    __construct: function (name, data) {
        parent.__construct(name, data);
        
        self.EVENT_SORTED = self.name + '-sorted';

        self.element.ondragover = function (event) {
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
            var marker = self.findMarker(event);
            if (marker) {
                if (marker.after === true) {
                    for (var i = self.target.length - 1; i >= 0; i --) {
                        self.element.insertBefore(self.target[i], marker.node.nextSibling);
                    }
                } else {
                    for (var i = 0; i < self.target.length; i ++) {
                        self.element.insertBefore(self.target[i], marker.node);
                    }
                }
                self.refreshTargetObject();
                Observer.fire(self.EVENT_SORTED);
            }
            self.removeMarker();
            return marker;
        };
        
        document.addEvent('click', function () {
            self.unselectAll();
        });
    },
    
    appendTo: function (node) {
        parent.appendTo(node);
        self.refreshTargetObject();
    },
       
    refreshTargetObject: function () {
        if (self.element.parentNode) {
            if (self.element.childNodes.length) {
                self.target_object = Dragdrop.createTargetObject([self.element], self.element.childNodes);
            } else {
                self.target_object = Dragdrop.createTargetObject([self.element]);
            }
        }
    },
    
    setTarget: function (nodes) {
        self.target = [];
        for (var i = 0; i < nodes.length; i ++) {
            self.target.push(nodes[i]);
        }
    },
    
    setChildren: function (structure) {
        parent.setChildren(structure);
        self.refreshTargetObject();
    },
    
    addNode: function (data) {
        parent.addNode(data);
        self.refreshTargetObject();
    },
    
    editNode: function (node, data) {
        node = parent.editNode(node, data);
        self.refreshTargetObject();
        return node;
    },
    
    removeNode: function (node) {
        parent.removeNode(node);
        self.refreshTargetObject();
    },

    createNode: function (data) {
        var node = parent.createNode(data);
        
        node.onmousedown = function (event) {
            event = Mohawk.DOM.event(event);

            if (event.button == BTN_LEFT) {
                if (!node.isSelected() || event.ctrlKey) {
                    self.select(event, node);
                }
                if (self.selected.length) {
                    self.setTarget(self.selected);
                } else {
                    self.setTarget([node]);
                }
                
                Dragdrop.setTargetObject(self.target_object);
    
                var clones = [];
                foreach(self.target, function () {
                    var clone = self.createClone(this);
                    clone.ondrop = function () {
                        this.remove();
                        this.proto.parent.object.target = [];
                    };
                    if (this == node) {
                        clone.onrelease = function (event) {
                            this.remove();
                            if (this.proto.onclick instanceof Function) {
                                this.proto.onclick.call(this, event);
                            }
                            this.proto.parent.object.target = [];
                        };
                    } else {
                        clone.onrelease = function (event) {
                            this.remove();
                            this.proto.parent.object.target = [];
                        };
                    }
                    
                    self.element.appendChild(clone);
                    clones.push(clone);
                });
                
                Dragdrop.pick(event, clones);
            }
        };
        
        node.onclick = function (event) {
            event = Mohawk.DOM.event(event);
            
            if (event.button == BTN_LEFT) {
                if (node.isSelected() && !event.ctrlKey) {
                    self.select(event, node);
                }
                if (node.ondblclick instanceof Function && !OPERA) {
                    var now = new Date;
                    if (node.last_click instanceof Date) {
                        if (now.getTime() - node.last_click.getTime() < 1000) {
                            node.ondblclick();
                        }
                    }
                    node.last_click = now;
                }
                event.stopPropagation();
            }
        };
        
        node.ondragover = function (event) {
            this.addClass('dragover');
        };
        
        node.ondragout = function (event) {
            this.removeClass('dragover');
        };
        
        node.ondrop = function (event) {
            this.removeClass('dragover');
        };
                        
        return node;
    },
    
    createClone: function (node) {
        var clone = node.cloneNode(true);
        clone.addClass(Dragdrop.css_class);
        clone.style.position = 'absolute';
        clone.style.left = node.offsetLeft - self.node_margin.x + 'px';
        clone.style.top = node.offsetTop - self.node_margin.y + 'px';
//        clone.style.left = node.coordinates().x - self.node_margin.x + 'px';
//        clone.style.top = node.coordinates().y - self.node_margin.y + 'px';
        clone.setOpacity(0.5);
        clone.clone = true;
        clone.proto = node;
        return clone;
    },
    
    findMarker: function (event) {
        DOM.event(event);
        
        var marker = true;
        var last = false;
        var prev = false;

        
        var width = self.element.firstChild.offsetWidth + self.node_margin.x * 2;
        var height = self.element.firstChild.offsetWidth + self.node_margin.y * 2;
        var in_a_row = Math.floor(self.element.offsetWidth / width);

        var cur = new Pixel(event.cursor().x - self.element.coordinates().x, event.cursor().y - self.element.coordinates().y);
        var x_num = Math.floor(cur.x / width);
        if (cur.x > in_a_row * width) {
            x_num = in_a_row - 1;
        }
        var y_num = Math.floor(cur.y / height);

        var cell_cur = new Pixel(cur.x - x_num * width, cur.y - y_num * height);
        if (cell_cur.y > self.node_margin.y && cell_cur.y < self.node_margin.y + self.element.firstChild.offsetHeight) {
            if (cell_cur.x > self.node_margin.x + self.element.firstChild.offsetWidth) {
                var after = true;
            } else if (cell_cur.x < self.node_margin.x) {
                var after = false;
            } else {
                return false;
            }
            var num = x_num + in_a_row * y_num;    
                    
            return {node: self.element.childNodes[num], after: after};
        } else {
            return false;
        }
    },
    
    createMarker: function (x, y) {
        if (!self.marker) {
            var marker = document.createElement('DIV');
            marker.addClass('marker');
            marker.style.position = 'absolute';
            marker.style.left = x + 'px';
            marker.style.top = y + 'px';
            document.body.appendChild(marker);
            self.marker = marker;
            Dragdrop.bringToFront(marker);
        }
    },
    
    setMarkerBefore: function (node) {
        var x = node.coordinates().x - self.node_margin.x - self.marker_size.width / 2;
        var y = node.coordinates().y + (node.offsetHeight - self.marker_size.height) / 2;
        self.createMarker(x, y);
    },
    
    setMarkerAfter: function (node) {
        var x = node.coordinates().x + node.offsetWidth + self.node_margin.x - self.marker_size.width / 2;
        var y = node.coordinates().y + (node.offsetHeight - self.marker_size.height) / 2;
        self.createMarker(x, y);
    },
    
    removeMarker: function () {
        if (self.marker) {
            self.marker.remove();
            self.marker = null;
        }
    }
    
});