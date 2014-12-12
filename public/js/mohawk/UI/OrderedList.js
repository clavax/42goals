include('mohawk.UI.List');

Mohawk.UI.OrderedList = Mohawk.UI.List.extend ({
    addNode: function (data) {
        var node = self.createNode(data);
        self.placeNode(node);
        return node;
    },
    
    editNode: function (node, data) {
        var node_data = node.data;
        foreach(data, function (i) {
            node_data[i] = data[i];
        });
        var new_node = self.createNode(node_data);
        node.setClassesTo(new_node);
        self.removeNode(node);
        self.placeNode(new_node);
        return new_node;
    },
    
    placeNode: function (node) {
        if (self.compareNodes instanceof Function) {
            var before = null;
            for (var i = 0; i < self.element.childNodes.length; i ++) {
                var child = self.element.childNodes[i];
                if (child == node) {
                    continue;
                }
                if (self.compareNodes(node, child)) {
                    before = child;
                    break;
                }
            }
            self.element.insertBefore(node, before);
        } else {
            self.element.appendChild(node, child);
        }
        
        if (node.previousSibling && node.previousSibling.isLast()) {
            node.previousSibling.notLast();
            node.setLast();
        } else if (self.element.childNodes.length == 1) {
            node.setLast();
        }
    }    
});