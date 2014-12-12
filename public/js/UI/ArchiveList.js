include('mohawk.UI.List');

window.ArchiveList = Mohawk.UI.List.extend({
	__construct: function (id, children) {
		parent.__construct(id, children);
		self.EVENT_EMPTY = self.id + '-empty';
		self.EVENT_NOTEMPTY = self.id + '-notempty';
	},
	
	createNode: function (data) {
		var node = parent.createNode(data);
		
		node.purgeChildren();
		
		var date = DOM.element('small');
		date.setHTML(Format.date(Date.fromString(data.archived), 'M d, Y'));
		date.appendTo(node);
		
		var title = DOM.element('span');
		title.setHTML('&mdash; ' + data.title);
		title.appendTo(node);
		
		var btn_restore = DOM.element('img', {
			src: URL.img + 'site/restore.png',
			className: ['btn', 'restore'],
			onclick: function () {
				Goals.restore(data.id);
			},
			appendTo: node
		});
		
		var btn_remove = DOM.element('img', {
			src: URL.img + 'site/trash.png',
			className: ['btn', 'remove'],
			onclick: function () {
				Goals.remove(data.id);
			},
			appendTo: node
		});
		
		return node;
	},
	
	addNode: function (data) {
		var node = parent.addNode(data);
		Observer.fire(self.EVENT_NOTEMPTY);
		return node;
	},
	
	removeNode: function (node) {
		var result = parent.removeNode(node);
		if (!self.element.childNodes.length) {
			Observer.fire(self.EVENT_EMPTY);
		}
		return result;
	}
});