Mohawk.UI.List = new Class ({
    id: '',
    element: null,
    onchange: function () {},

    __construct: function (id, structure) {
        self.id = id;

        self.element = document.createElement('UL');
        self.element.id = id;
        self.element.object = self;

        self.setChildren(structure);
    },
    
    setChildren: function (structure) {
        self.element.removeChildren();
        for (var i = 0; i < structure.length; i ++) {
        	self.addNode(structure[i]);
        }
    },

    createNode: function (data) {
        var node = document.createElement('LI');
        node.id = self.getId(data.id);
        node.data = data;
        node.parent = self.element;
        node.innerHTML = data.title;
        node.getId = function () {
            return this.id;
        };
        
        node.isLast = function () {
            return node.hasClass('last');
        };
        node.setLast = function () {
            node.addClass('last');
        };
        node.notLast = function () {
            node.removeClass('last');
        };
        
        node.onmouseover = function (event) {
            node.addClass('over');
        };
        
        node.onmouseout = function (event) {
            node.removeClass('over');
        };
        
        return node;
    },

    getId: function (id) {
        return self.id + '-' + id;
    },

    getNode: function (id) {
        var node = null;
        var id = self.getId(id);
        if (self.element.parentNode) {
            node = ID(id);
        } else {
            for (var i = 0; i < self.element.childNodes.length; i ++) {
                if (self.element.childNodes[i].id == id) {
                    node = self.element.childNodes[i];
                    break;
                }
            }
        }
        
        return node;
    },

    addNode: function (data) {
        var node = self.createNode(data);
        self.element.appendChild(node);

        if (node.previousSibling) {
            node.previousSibling.notLast();
        }
        
        node.setLast();
        return node;
    },

    editNode: function (node, data) {
        var node_data = node.data;
        foreach(data, function (i) {
            node_data[i] = data[i];
        });
        var new_node = self.createNode(node_data);
        node.setClassesTo(new_node);
        node.replace(new_node);
        return new_node;
    },

    removeNode: function (node) {
        if (node.isLast()) {
            if (node.previousSibling) {
                node.previousSibling.setLast();
            }
        }
        node.remove();
    },
    
    appendTo: function (node) {
        node.appendChild(self.element);
    }
});