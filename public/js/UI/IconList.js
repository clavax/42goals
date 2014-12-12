include('mohawk.UI.SelectableList');

window.IconList = Mohawk.UI.SelectableList.extend({
    multiple: false,
    
    __construct: function (id, data) {
        parent.__construct(id, data);
        self.element.addClass('icon-list');
        self.selected = [];
    },
    
    createNode: function (data) {
        var node = parent.createNode(data);
        node.setHTML('');
        
        var icon = DOM.element('IMG');
        icon.src = data.src;
        node.appendChild(icon);

        return node;
    },
    
    getSelected: function () {
        var data = null;
        if (self.selected.length) {
            data = self.selected[0].data.id;
        }
        return data;
    },
    
    setSelected: function (id) {
        self.unselectAll();
        var node = self.getNode(id);
        if (node) {
            node.select();
        }
    }
});