window.MeLayout = Mohawk.UI.List.extend({
    createNode: function (data) {
        var node = parent.createNode(data);
        node.setHTML('');
        
        if (data.size) {
            node.addClass(data.size);
        }
        if (!data.chart) {
            node.addClass('empty');
        }
        
        node.clear = function () {
            node.data.chart = null;
            node.canvas.setHTML('');
            node.header.setHTML('');
            node.addClass('empty');
        };
        
        node.canvas = DOM.element('div', {
            id: node.id + '-canvas',
            className: 'canvas',
            appendTo: node
        });
        
        node.header = DOM.element('h2', {
            html: data.chart ? data.chart.title : '',
            appendTo: node
        });
        
        node.btn_add = DOM.element('a', {
            href: '#add',
            html: 'Add chart',
            className: 'add',
            appendTo: node,
            onclick: function () {
                Me.Constructor.set(node);
                return false;
            }
        });
        
        node.toolbar = DOM.element('div', {
            className: 'toolbar',
            appendTo: node
        });
        
        node.btn_edit = DOM.element('img', {
            src: URL.img + 'site/chart-edit.png',
            className: 'edit',
            appendTo: node.toolbar,
            onclick: function () {
                Me.Constructor.set(node);
                return false;
            }
        });
        
        node.btn_remove = DOM.element('img', {
            src: URL.img + 'site/chart-delete.png',
            className: 'remove',
            appendTo: node.toolbar,
            onclick: function (event) {
                event = DOM.event(event);
                Charts.remove(data.chart.id);
                event.stopPropagation();
                return false;
            }
        });
        
        return node;
    }    
});