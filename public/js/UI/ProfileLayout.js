window.ProfileLayout = Mohawk.UI.List.extend({
    createNode: function (data) {
        var node = parent.createNode(data);
        node.setHTML('');
        
        if (data.size) {
            node.addClass(data.size);
        }
        node.addClass('static');
        
        node.canvas = DOM.element('div', {
            id: node.id + '-canvas',
            className: 'canvas',
            appendTo: node
        });
        
        node.header = DOM.element('h2', {
            html: data.chart ? data.chart.title : '',
            appendTo: node
        });
        
        return node;
    }    
});