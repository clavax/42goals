include('UI.IconPick');
include('UI.TemplatesPick');
include('mohawk.UI.Tabset');

window.GoalsForm = new Class({
    element: null,
    
    __construct: function () {
        self.element = self.createForm();
    
        document.body.appendChild(self.element);
        self.element.addClass('hidden');
        
        var icons = [];
        foreach(Data.icons, function (i, val) {
            icons.push({id: i, src: val});
        });
        self.IconPick = new IconPick(icons);

        self.IconItem = new IconPicker(self.IconPick);
        ID('goals-icon-item').replace(self.IconItem.element);

        self.IconTrue = new IconPicker(self.IconPick);
        ID('goals-icon-true').replace(self.IconTrue.element);

        self.IconFalse = new IconPicker(self.IconPick);
        ID('goals-icon-false').replace(self.IconFalse.element);
    },
    
    createForm: function () {
        var form = DOM.element('FORM');
        form.object = self;
        
        Loader.includeTemplate('goals-form');
        form.id = 'goals-form';
        form.setHTML(Template.transform(GOALS_FORM));
        
        form.onmousedown = DOM.stopEvent;
        form.onsubmit = function () {
            self.save.apply(self, [form]);
            return false;
        };

//        self.Tabset = Mohawk.UI.Tabset.fromElement(form.getElementsByTagName('DL')[0]);
//        form.getElementsByTagName('DL')[0].replace(self.Tabset.element);
//        self.Tabset.tabs[0].content.id = 'tab-templates';
//        self.Tabset.tabs[1].content.id = 'tab-advanced';

//        self.Templates = new TemplatesPick('templates-pick', Data.templates);
//        form.getElementsByClassName('templates-pick')[0].replace(self.Templates.element);
        
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
        data.icon_true = self.IconTrue.data;
        data.icon_false = self.IconFalse.data;
        data.prepend = data.prepend || 'no';
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
            self.setType(data.type);
            self.IconItem.setData(data.icon_item);
            self.IconTrue.setData(data.icon_true);
            self.IconFalse.setData(data.icon_false);
//            self.Tabset.tabs[1].title.open();
        } else {
            self.element.replaceClass('editing', 'adding');
            self.element.setData({type: 'numeric'});
            self.setType('numeric');
            self.IconItem.setData(0);
            self.IconTrue.setData(0);
            self.IconFalse.setData(0);
//            self.Tabset.tabs[0].title.open();
            self.Templates.setChildren(Data.templates);
        }
        ID('additional-parameters').addClass('hidden');
    },
    
    hide: function () {
        Shadow.hide();
        self.element.addClass('hidden');
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