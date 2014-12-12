window.MeGoals = Mohawk.UI.List.extend({
    createNode: function (data) {
        var node = parent.createNode(data);
        node.setHTML('');
        
        node.addClass(data.privacy);
        
        var btn_private = DOM.element('img', {
            src: URL.img + 'site/btn-private.png',
            appendTo: node,
            className: ['btn', 'private'],
            onclick: function () {
                node.replaceClass('private', 'loading');
                Goals.edit(node.data.id, {privacy: 'public'});
            }
        });
        
        var btn_public = DOM.element('img', {
            src: URL.img + 'site/btn-public.png',
            appendTo: node,
            className: ['btn', 'public'],
            onclick: function () {
                node.replaceClass('public', 'loading');
                Goals.edit(data.id, {privacy: 'private'});
            }
        });
        
        var progress = DOM.element('img', {
            src: URL.img + 'site/loading24.gif',
            appendTo: node,
            className: ['btn', 'progress']
        });
        
        var title = DOM.element('h2', {
            html: data.title,
            appendTo: node
        });
        
        return node;
    }
});