include('mohawk.UI.OrderedList');
include('UI.TemplatesPick');

window.CategoriesPick = Mohawk.UI.OrderedList.extend({
    active: null,
    
    createNode: function(data) {
        var node = parent.createNode(data);
        node.purgeChildren();
        
        var title = DOM.element('H2', {
        	appendTo: node
        });
        
        var span = DOM.element('SPAN', {
        	innerHTML: data.title,
        	appendTo: title
        });
        
        var templates = [];
        foreach(Data.templates, function (i, template) {
        	if (template.category == data.id) {
        		templates.push(template);
        	}
        });
        node.Templates = new TemplatesPick('templates-' + node.id, templates);
        var list = node.Templates.element;
        
        list.addClass('hidden');
        node.appendChild(list);
        
        span.onclick = function () {
            if (list.hasClass('hidden')) {
                if (self.active) {
                    Effects.fold(self.active, function () {
                        self.active.addClass('hidden');
                        list.removeClass('hidden');
                        Effects.unfold(list);
                        self.active = list;
                    });
                } else {
                	list.removeClass('hidden');
                    Effects.unfold(list);
                    self.active = list;
                }
            } else {
                Effects.fold(list, function () {
                	list.addClass('hidden');
                    self.active = null;
                });
            }
        };

        return node;
    }
});