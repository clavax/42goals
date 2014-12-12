window.CategoriesForm = new Class({
    element: null,
    
    __construct: function () {
        self.element = self.createForm();
    
        document.body.appendChild(self.element);
        self.element.addClass('hidden');
    },
    
    createForm: function () {
        var form = DOM.element('FORM');
        form.object = self;
        
        Loader.includeTemplate('categories-form');
        form.id = 'categories-form';
        form.setHTML(Template.transform(CATEGORIES_FORM));
        
        form.onmousedown = DOM.stopEvent;
        form.onsubmit = function () {
            self.save.apply(self, [form]);
            return false;
        };

        return form;
    },
    
    save: function (form) {
        foreach(form.getElementsByClassName('error'), 
            function () {
                this.remove();
            }
        );
        
        var data = form.getData();
        Observer.fire('categories-submitted', data);
    },

    set: function (data) {
        self.element.removeClass('hidden');
        Shadow.show();
        Dragdrop.bringToFront(self.element);
        self.element.setData({'id': ''});
        self.element.reset();
        if (data) {
        	self.element.setData(data);
        }        	
    },
    
    hide: function () {
        Shadow.hide();
        self.element.addClass('hidden');
    }
});