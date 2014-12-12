include('UI.IconPick');

window.GoalsPage = new Class({
    element: null,
    
    __construct: function () {
        self.element = self.createForm();
    
        ID('goals-wrapper').appendChild(self.element);
        self.element.addClass('hidden');
        
        var icons = [];
        foreach(Data.icons, function (i, val) {
            icons.push({id: i, src: val});
        });
        self.IconPick = new IconPick(icons);

        self.IconItem = new IconPicker(self.IconPick);
        self.element.getElementsByClassName('goals-icon-item')[0].replace(self.IconItem.element);
        
        self.IconZero = new IconPicker(self.IconPick);
//        self.element.getElementsByClassName('goals-icon-zero')[0].replace(self.IconZero.element);

        self.IconTrue = new IconPicker(self.IconPick);
        self.element.getElementsByClassName('goals-icon-true')[0].replace(self.IconTrue.element);

        self.IconFalse = new IconPicker(self.IconPick);
        self.element.getElementsByClassName('goals-icon-false')[0].replace(self.IconFalse.element);
    },
    
    createForm: function () {
        var form = DOM.element('FORM');
        form.addClass('goals-form');
        form.object = self;
        
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
        data.icon_item = self.IconItem.data;
        data.icon_zero = self.IconZero.data;
        data.icon_true = self.IconTrue.data;
        data.icon_false = self.IconFalse.data;
        data.prepend = data.prepend || 'no';
        Observer.fire('form-submitted', data);
    },

    setData: function () {
        self.element.removeClass('hidden');

//        Dragdrop.bringToFront(self.element);
        self.element.setData({'id': ''});
        self.element.reset();
        
        self.element.getElementsByClassName('additional-parameters')[0].addClass('hidden');
        
        Observer.fire(self.EVENT_SET);
    },
    
    hide: function (silently) {
        self.element.addClass('hidden');
        if (!silently) {
            Observer.fire(self.EVENT_HIDDEN);
        }
    },
    
    setType: function (type) {
        var fields = self.element.getElementsByTagName('LI');
        for (var i = 0; i < fields.length; i ++) {
            if (!fields[i].hasClass('type')) {
                continue;
            }
            if (type && fields[i].hasClass(type)) {
                fields[i].removeClass('hidden');
            } else {
                fields[i].addClass('hidden');
            }
        }
    }
});