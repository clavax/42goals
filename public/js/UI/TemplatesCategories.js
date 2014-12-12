include('mohawk.UI.SortableList');

window.TemplatesCategories = Mohawk.UI.SortableList.extend({
	orientation: 'horizontal',
	marker_class: 'tabs-marker',
	marker_size: {width: 16, height: 16},
	editing: null,
	
    createNode: function (data) {
		var node = parent.createNode(data);
		node.ondragover = function () {
			this.addClass('dragover');
		};
		node.ondragout = function () {
			this.removeClass('dragover');
		};
		node.ondrop = function () {
			Templates.edit(Templates.List.clone.proto.data.id, {category: this.data.id});
			this.removeClass('dragover');
		};
		
		node.purgeChildren();
		
        var btn_move = DOM.element('IMG');
        btn_move.src = URL.img + 'site/move.png';
        btn_move.addClass('btn', 'move', 'default');
        btn_move.onmousedown = node.onmousedown;
        btn_move.appendTo(node);
        
        var title = DOM.element('A');
        title.href = URL['this'] + '#category-' + node.data.id;
        title.setHTML(data['title_' + ENV.language] || '-- no title --');
        title.appendTo(node);
        
        var btn_edit = DOM.element('IMG');
        btn_edit.src = URL.img + 'site/edit.gif';
        btn_edit.addClass('btn', 'edit', 'default');
        btn_edit.appendTo(node);
        btn_edit.onclick = function () {
        	Categories.Form.set(data);
        };
        
        node.onmousedown = DOM.stopEvent;
        node.onclick = DOM.stopEvent;
        
        return node;
    },
    
    hide: function () {
    	if (self.editing) {
    		self.editing.removeClass('mode-editing');
    		self.editing = null;
    	}
    },
    
    compareNodes: function (node1, node2) {
    	return node1.data.position <= node2.data.position;
    },
    
    select: function (node) {
    	if (self.current) {
    		self.current.removeClass('current');
    	}
    	if (node) {
    		node.addClass('current');
    	}
    	self.current = node;
    },
    
    unselect: function () {
    	self.select(null);
    }
});