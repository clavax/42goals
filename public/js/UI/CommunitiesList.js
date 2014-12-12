include('mohawk.UI.List');

window.CommunitiesList = Mohawk.UI.List.extend({
    createNode: function (data) {
        var node = parent.createNode(data);
        node.setHTML('');
        
        var title = DOM.element('a', {
            href: '#' + data.id,
            className: 'script',
            html: data.title,
            appendTo: node
        });
        
        title.onclick = function () {
            Communities.Form.set(data);
        };
        return node;
    }
});