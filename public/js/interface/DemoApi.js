include('interface.GoalsApi');

window.DemoApi = GoalsApi.extend({
    add: function (data) {
        try {
            if (!data.title) {
                self.showErrors(Goals.Add, {title: LNG.Error_empty_title});
                return;
            }
            data.id = Data.goals.length + 2;
            data.user = ENV.UID;
            data = self.handleIcons(data);
            Observer.fire('goal-added', data);
        } catch (e) {
            // console.log(e);
        }
    },
    
    edit: function (id, data) {
        try {
	        if (data.title !== undefined && !data.title) {
	            self.showErrors(Goals.Edit, {title: LNG.Error_empty_title});
	            return;
	        }
            data = self.handleIcons(data);
            data.id = id;
            data.user = ENV.UID;
            Observer.fire('goal-edited', data);
        } catch (e) {
            // console.log(e);
        }
    },
    
    handleIcons: function (data) {
        var id = Object.keys(Data.icons).sort(function (a, b) {return a * 1 - b * 1;}).pop() * 1; // get the largest key
        foreach(['icon_item', 'icon_yes', 'icon_no'], function (i, type) {
            if (data[type] && data[type].toString().match(/^http:\/\//)) {
                id ++;
                Data.icons[id] = data[type];
                data[type] = id;
            }
        });        
        return data;
    },
    
    remove: function (id) {
        Observer.fire('goal-removed', id);
    },

    archive: function (id) {
        self.edit(id, {archived: Data.today});
    },
    
    restore: function (id) {
        self.edit(id, {archived: null});
    },
    
    sort: function () {
        // do nothing
    },
    
    save: function (id, date, data) {
        data.goal = id;
        data.date = date;
        self.setData(id, date, data);
        
        var row = ID('goal-' + id);
        Goals.Table.createChart(row.data, row.getElementsByClassName('chart')[0]);
        Goals.Table.aggregate(row.data, row.getElementsByClassName('total')[0]);       
        Goals.Table.setPlan(row);
    },
    

    addPlan: function (data) {
        self.setPlan(data);
        self.Table.setPlan(ID('goal-' + data.goal));
        var row = ID('goal-' + data.goal);
        Goals.Table.aggregate(row.data, row.getElementsByClassName('total')[0]);
        Observer.fire('plan-added-' + data.id, data.id);
    },
    
    editPlan: function (id, data) {
        data.id = id;
        self.setPlan(data);
        var row = ID('goal-' + data.goal);
        Goals.Table.aggregate(row.data, row.getElementsByClassName('total')[0]);
        self.Table.setPlan(row);
    },

    removePlan: function (id, goal) {
        self.unsetPlan(goal, id);
        self.Table.setPlan(ID('goal-' + goal));
    }
});

window.DemoTabsApi = new Class({
    add: function (data) {
        Observer.fire('tab-added', data);
    },
    
    edit: function (id, data) {
    	data.id = id;
        Observer.fire('tab-edited', data);
    },
    
    remove: function (id) {
        Observer.fire('tab-removed', id);
    },
    
    create: function () {
        var data = {};
        data.id = Math.rand(1e10, 1e12);
        data.title = prompt(LNG.Input_new_tab_name);
        data.position = parseInt(Goals.Tabs.element.lastChild ? Goals.Tabs.element.lastChild.data.position : 0) + 1;
        if (data.title === null || !data.title.length) {
            return;
        }
        self.add(data);
    },
    
    sort: function () {

    }
});