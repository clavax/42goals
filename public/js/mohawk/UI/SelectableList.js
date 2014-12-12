include('mohawk.UI.List');

Mohawk.UI.SelectableList = Mohawk.UI.List.extend(
Mohawk.UI.SelectableListInterface = {
    selected: [],
    last_selected: null,
    last_click: null,
    multiple: true,

    __construct: function (id, structure) {
        parent.__construct(id, structure);
        
        Mohawk.UI.selectables.push(self);
        
        self.EVENT_SELECTED = self.id + '-selected';

        // prevent selecting text
        self.element.onmousedown = function () {
        	return false;
        };
    },
    
    createNode: function (data) {
        var node = parent.createNode(data);
        
        node.select = function () {
            node.addClass('selected');
            if (!node.isSelected()) {
                self.selected.push(node);
            }
        };
        
        node.unselect = function () {
            node.removeClass('selected');
            Array.remove(self.selected, node);
        };

        node.isSelected = function () {
            return Array.find(self.selected, node) !== false;
        };
        
        node.onclick = function (event) {
        	event = DOM.event(event);
        	
            if (IE ? event.button == 0 : event.button == BTN_LEFT) {
                if (node.isSelected() && !event.ctrlKey) {
                    self.select(event, node);
                }
                if (node.ondblclick instanceof Function) {
                    var now = new Date;
                    if (node.last_click instanceof Date && !OPERA) {
                        if (now.getTime() - node.last_click.getTime() < 1000) {
                            node.ondblclick();
                        }
                    }
                    node.last_click = now;
                }
                event.stopPropagation();
                self.select(event, node);
            }
        };
        
        return node;
    },
    
    select: function (event, node, silently) {
        event = DOM.event(event);
        
        if (event.ctrlKey && self.multiple) {
            if (Array.find(self.selected, node) !== false) {
                node.unselect();
            } else {
                node.select();
                self.last_selected = node;
            }
        } else if ((event.shiftKey && self.last_selected && self.multiple) || Mohawk.UI.selection) {
            if (Mohawk.UI.selection && !self.last_selected) {
                self.last_selected = node;
            }
            
            var last = 0;
            var cur = 0;
            for (var i = 0; i < self.element.childNodes.length; i ++) {
                if (self.element.childNodes[i] == self.last_selected) {
                    last = i;
                }
                if (self.element.childNodes[i] == node) {
                    cur = i;
                }
                if (cur && last) {
                    break;
                }
            }

            while (self.selected.length) {
                self.selected[0].unselect();
            }

            if (cur > last) {
                for (var i = last; i < cur; i ++) {
                    self.element.childNodes[i].select();
                }
            } else {
                for (var i = cur; i <= last; i ++) {
                    self.element.childNodes[i].select();
                }
            }

            if (!node.isSelected()) {
                node.select();
            }
        } else {
            self.unselectAll();
            node.select();
            self.last_selected = node;
        }
        if (!silently) {
            Observer.fire(self.EVENT_SELECTED);
        }
    },

    unselectAll: function () {
        while (self.selected.length) {
            self.selected[0].unselect();
        }
        self.last_selected = null;
    },

    isSelected: function (node) {
        return Array.find(self.selected, node) !== false;
    },
    
    setSelected: function (id) {
        self.select({}, self.getNode(id), true);
    },
    
    getSelected: function () {
        return self.selected.length ? self.selected[0].data.id : false;
    }
});