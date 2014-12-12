include('mohawk.UI.List');

Mohawk.Loader.addCss('context-menu.css');

Mohawk.UI.ContextMenu = Mohawk.UI.List.extend ({
    __construct: function (name, structure) {
        parent.__construct(name, structure);
        self.element.addClass('context-menu');
        self.element.onmouseup = function (event) {
        	event = Mohawk.DOM.event(event);
        	if (event.button == BTN_RIGHT) {
        		event.preventDefault();
        		event.stopPropagation();
        	}
        	return false;
        };
    },
    
    createNode: function (data) {
        var node = parent.createNode(data);

        var link = document.createElement('A');
        node.link = link;
        link.href = 'click-to: ' + data.title;
        link.action = data.action;
        if (node.data.disabled) {
            node.addClass('disabled');
            link.onclick = function () {
            	return false;
        	};
        } else {
            link.onclick = function () {
            	this.action.call();
            	return false;
        	};
        }
        link.innerHTML = node.innerHTML;
        node.innerHTML = '';
        node.appendChild(link);
        
        return node;
    },
    
    append: function (event) {
    	event = Mohawk.DOM.event(event);
        
        var cur = event.cursor();
        var doc = document.size();

        document.body.appendChild(self.element);
        self.element.style.left = '-1000px'; // in order to get element size
        
        if (cur.x + self.element.offsetWidth > doc.width) {
            self.element.style.left = (cur.x - self.element.offsetWidth - 3) + 'px';
        } else {
            self.element.style.left = cur.x + 3 + 'px';
        }

        if (cur.y + self.element.offsetHeight > doc.height) {
            self.element.style.top = (cur.y - self.element.offsetHeight) + 'px';
        } else {
            self.element.style.top = cur.y + 'px';
        }

        if (Mohawk.contextmenu) {
            Mohawk.contextmenu.remove();
            Mohawk.contextmenu = null;
        }
        Mohawk.contextmenu = self.element;
        
        document.addEvent('click', Mohawk.UI.ContextMenu.remove);
        Dragdrop.bringToFront(self.element);
    }
});

Mohawk.UI.ContextMenu.remove = function () {
    if (Mohawk.contextmenu) {
        Mohawk.contextmenu.remove();
        Mohawk.contextmenu = null;
    }
    document.removeEvent('click', Mohawk.UI.ContextMenu.remove);
}