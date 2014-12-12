include('UI.CategoriesPick');
include('UI.GoalsPage');
include('mohawk.UI.Tabset');

window.GoalsPageAdd = GoalsPage.extend({
    createForm: function () {
        var form = parent.createForm();
        form.id = 'goals-form-add';
        self.EVENT_SET = form.id + '-set';
        self.EVENT_HIDDEN = form.id + '-hidden';
        
        Loader.includeTemplate('goals-form-add');        
        form.setHTML(Template.transform(GOALS_FORM_ADD));

        self.Tabset = Mohawk.UI.Tabset.fromElement(form.getElementsByTagName('DL')[0]);
        form.getElementsByTagName('DL')[0].replace(self.Tabset.element);

        self.Categories = new CategoriesPick('categories-pick', Data.categories);
        form.getElementsByClassName('categories-pick')[0].replace(self.Categories.element);
        
        return form;
    },
    
    setData: function () {
        parent.setData();
        self.element.replaceClass('editing', 'adding');
        self.element.setData({
        	type: 'numeric',
        	tab: ENV.tab,
        	position: (Goals.Table.table.tBodies[0].length ? parseInt(Goals.Table.table.tBodies[0].lastChild.data.position) : 0) + 1
    	});
        self.setType('numeric');
        self.IconItem.setData(0);
        self.IconZero.setData(0);
        self.IconTrue.setData(0);
        self.IconFalse.setData(0);
        self.Tabset.tabs[0].title.open();
        // self.Categories.setChildren(Data.categories);
    }
});