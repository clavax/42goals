include('mohawk.UI.SortableList');

window.GoalsTabs = Mohawk.UI.SortableList.extend({
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
			Goals.edit(Goals.Table.clone.proto.parentNode.data.id, {tab: this.data.id});
			this.removeClass('dragover');
		};
		
		node.purgeChildren();
		
		if (ENV.user.valid) {
	        var btn_move = DOM.element('IMG');
	        btn_move.src = URL.img + 'site/move.png';
	        btn_move.addClass('btn', 'move', 'default');
	        btn_move.onmousedown = node.onmousedown;
	        btn_move.appendTo(node);
	        
	        var title = DOM.element('A');
	        title.href = URL['this'] + '#tab-' + node.data.id;
	        title.addClass('default');
	        title.setHTML(data.title);
	        title.appendTo(node);
	        
	        var btn_edit = DOM.element('IMG');
	        btn_edit.src = URL.img + 'site/edit.gif';
	        btn_edit.addClass('btn', 'edit', 'default');
	        btn_edit.appendTo(node);
	        btn_edit.onclick = function () {
	        	self.hide();
	        	node.addClass('mode-editing');
	        	node.input.value = node.data.title;
	        	node.input.focus();
	        	self.editing = node;
	        };
	        
	        // editing form
	        var input = DOM.element('input', {type: 'text'});
	        input.id = self.id + '-input';
	        input.addClass('editing');
	        input.onkeypress = function (event) {
	            event = DOM.event(event);
	            if (event.key() == 13) {
	                Tabs.edit(node.data.id, {title: input.value});
	            }
	        };
	        node.input = input;
	        node.appendChild(input);
	        
	        var enter = DOM.element('IMG');
	        enter.src = URL.img + 'site/enter.png';
	        enter.onclick = function () {
	        	Tabs.edit(node.data.id, {title: input.value});
	        };
	        enter.addClass('enter', 'editing');
	        node.appendChild(enter);
	        
	        var trash = DOM.element('IMG');
	        trash.src = URL.img + 'site/trash.png';
	        trash.onclick = function () {
	        	Tabs.remove(node.data.id);
	        };
	        trash.addClass('trash', 'editing');
	        // node.appendChild(trash);
		} else {
	        var title = DOM.element('A');
	        title.href = URL['home'] + 'premium/';
	        title.addClass('disabled');
	        title.setHTML(data.title);
	        title.appendTo(node);
	        title.onclick = show_premium;
		}
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