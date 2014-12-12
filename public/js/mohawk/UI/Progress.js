include('mohawk.kernel.Dragdrop');
include('mohawk.kernel.Effects');

Mohawk.UI.Progress = new Class ({
    element: null,
    text: null,
    loading: false,
    
    __construct: function () {
        var element = document.createElement('DIV');
        element.id = 'progress';
        self.element = element;
        self.element.onmouseover = function () {
        	if (!self.loading) {
        		self.element.collapse(Mohawk.Effects.vanish);
        	}
        }
        var text = document.createElement('P');
        element.appendChild(text);
        self.text = text;
        self.element.collapse();
    },
    
    appendTo: function (node) {
        node.appendChild(self.element);
    },
    
    append: function () {
        self.appendTo(document.body);
    },
    
    load: function (text) {
        self.element.display();
        Mohawk.Dragdrop.bringToFront(self.element);
        self.text.innerHTML = text;
        self.text.addClass('active');
        self.loading = true;
    },
    
    done: function (text, hide) {
        self.element.display();
        Mohawk.Dragdrop.bringToFront(self.element);
        self.text.setHTML(text);
        self.text.removeClass('active');
        if (hide) {
        	self.element.collapse(Mohawk.Effects.vanish);
        }
        self.loading = false;
    }
});