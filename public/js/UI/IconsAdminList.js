include('mohawk.UI.DraggableList');

window.IconsAdminList = Mohawk.UI.DraggableList.extend({
    node_margin: new Pixel(5, 5),
    marker_size: {width: 6, height: 100},
    
    createNode: function (data) {
        var node = parent.createNode(data);
        node.addClass('rounded');
        
        var img = DOM.element('IMG');
        img.src = data.src;
        img.addClass('icon');
        node.setHTML('');
        node.appendChild(img);
        
        var progress = DOM.element('DIV');
        progress.addClass('progress');
        node.appendChild(progress);
        
        node.setStatus = function (status) {
            progress.setHTML(status);
        };

        return node;
    }
});