include('UI.TemplatesPick');
include('UI.GoalsPage');
include('UI.PlanTable');
include('UI.GoalsChart');
include('mohawk.UI.Tabset');

window.GoalsPageEdit = GoalsPage.extend({
    createForm: function () {
        var form = parent.createForm();
        form.id = 'goals-form-edit';
        self.EVENT_SET = form.id + '-set';
        self.EVENT_HIDDEN = form.id + '-hidden';
        
        Loader.includeTemplate('goals-form-edit');
        form.setHTML(Template.transform(GOALS_FORM_EDIT));
        
        self.Tabset = Mohawk.UI.Tabset.fromElement(form.getElementsByTagName('DL')[0]);
        form.getElementsByTagName('DL')[0].replace(self.Tabset.element);
        self.Plan = new PlanTable('goals-plan');
        form.getElementsByClassName('goals-plan')[0].replace(self.Plan.element);
        
        self.Chart = new GoalsChart;
        
        return form;
    },
    
    setData: function (data) {
        parent.setData();
        self.data = data;
        
        // set data
        self.element.setData(data);
        self.setType(data.type);
        
        // set icons
        self.IconItem.setData(data.icon_item);
        self.IconZero.setData(data.icon_zero);
        self.IconTrue.setData(data.icon_true);
        self.IconFalse.setData(data.icon_false);
        
        // open first tab
        self.Tabset.tabs[0].title.open();
        
        // set planning data
        self.Plan.goal = data;
        var plan = Data.plan[data.id] || {};
        self.Plan.setRows(Object.values(plan));

        // set chart
        if (!self.Chart.initiated) {
            self.Chart.init();
        }
        self.Chart.draw(data);
    },
    
    showChart: function (data) {
        self.setData(data);
        self.Tabset.tabs[2].title.open();
    }
});