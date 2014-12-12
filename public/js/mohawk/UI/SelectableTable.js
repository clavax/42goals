include('mohawk.UI.Table');

Mohawk.UI.SelectableTable = Mohawk.UI.Table.extend({
    selected: [],
    last_selected: null,

    __construct: function (id, data, head) {
        parent.__construct(id, data, head);
        
        Mohawk.UI.selectables.push(self);

        // prevent selecting text
        self.element.onmousedown = function () {
            return false;
        };
    },
    
    createRow: function (id, data) {
        var row = parent.createRow(id, data);
        
        row.select = function () {
            row.addClass('selected');
            if (!row.isSelected()) {
                self.selected.push(row);
            }
        };
        
        row.unselect = function () {
            row.removeClass('selected');
            Array.remove(self.selected, row);
        };

        row.isSelected = function () {
            return Array.find(self.selected, row) !== false;
        };

        row.onclick = function (event) {
            Mohawk.DOM.event(event);
            
            if (event.button == BTN_LEFT) {
                if (row.isSelected() && !event.ctrlKey) {
                    self.select(event, row);
                }
                event.stopPropagation();
                self.select(event, row);
            }
        };
        
        return row;
    },

    select: function (event, node) {
        Mohawk.DOM.event(event);
        
        if (event.ctrlKey) {
            if (Array.find(self.selected, node) !== false) {
                node.unselect();
            } else {
                node.select();
                self.last_selected = node;
            }
        } else if (event.shiftKey && self.last_selected) {
            if (!self.last_selected) {
                self.last_selected = node;
            }
            
            var last = 0;
            var cur = 0;
            for (var i = 0; i < self.element.tBodies[0].childNodes.length; i ++) {
                if (self.element.tBodies[0].childNodes[i] == self.last_selected) {
                    last = i;
                }
                if (self.element.tBodies[0].childNodes[i] == node) {
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
                    self.element.tBodies[0].childNodes[i].select();
                }
            } else {
                for (var i = cur; i <= last; i ++) {
                    self.element.tBodies[0].childNodes[i].select();
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
    },

    unselectAll: function () {
        while (self.selected.length) {
            self.selected[0].unselect();
        }
        self.last_selected = null;
    },

    isSelected: function (node) {
        return Array.find(self.selected, node) !== false;
    }    
});