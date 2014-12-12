window.PostsForm = new Class ({
    element: null,
    
    __construct: function () {
        self.element = self.createForm();
    
        document.body.appendChild(self.element);
        self.element.addClass('hidden');
    },
    
    createForm: function () {
        var form = document.createElement('FORM');
        form.object = self;
        
        Loader.includeTemplate('posts-form');
        form.setHTML(Template.transform(POSTS_FORM));
        form.id = 'posts-form';
        
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
        Observer.fire('form-submitted', data);
    },
    
    set: function (data) {
        self.element.removeClass('hidden');
        Shadow.show();
        Dragdrop.bringToFront(self.element);
        self.element.setData({'id': ''});
        self.element.reset();
        if (data) {
            self.element.replaceClass('adding', 'editing');
            self.element.setData(data);
        } else {
            self.element.replaceClass('editing', 'adding');
            self.element.setData({date: (new Date()).getId()});
        }
    },
    
    hide: function () {
        Shadow.hide();
        self.element.addClass('hidden');
    }
});