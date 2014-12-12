include('mohawk.UI.List');

window.PostsList = Mohawk.UI.List.extend({
    createNode: function (data) {
        var node = parent.createNode(data);
        node.setHTML('');
        
        var title = DOM.element('A');
        title.href = '#' + data.id;
        title.addClass('script');
        title.setHTML(data.title);
        node.appendChild(title);
        
        title.onclick = function () {
            Posts.Form.set(data);
        };
        return node;
    }
});