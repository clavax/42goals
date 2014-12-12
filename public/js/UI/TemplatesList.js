include('mohawk.UI.SortableList');

window.TemplatesList = Mohawk.UI.SortableList.extend({
	orientation: 'vertical',
	movable: true,
	marker_class: 'templates-marker',
	
    createNode: function (data) {
        var node = parent.createNode(data);
        node.setHTML('');
        
        node.onmousedown = function (event) {
            event = DOM.event(event);
            if (event.button != BTN_LEFT) {
                return;
            }
            self.clone = self.createClone(node);
            var categories = [self.element];
            for (var i = 0; i < Templates.Categories.element.childNodes.length; i ++) {
            	categories.push(Templates.Categories.element.childNodes[i]);
            };
            Dragdrop.setTarget.apply(Dragdrop, categories);
            self.element.appendChild(self.clone);
            Dragdrop.pick(event, [self.clone]);
            return false;
        };
        
        var title = DOM.element('A');
        title.href = '#' + data.id;
        title.addClass('script');
        title.setHTML(data.title);
        node.appendChild(title);
        
        title.onmousedown = DOM.stopEvent;
        title.onclick = function () {
            Templates.Form.set(data);
            return false;
        };
        return node;
    }
});