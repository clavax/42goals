include('utils.common');
include('interface.DemoApi');
include('UI.GoalsPageAdd');
include('UI.GoalsPageEdit');
include('UI.GoalsNumber');
include('UI.GoalsTime');
include('UI.GoalsWeek');
include('UI.GoalsChart');
include('UI.GoalsComment');
include('UI.Shadow');
include('utils.AnchorObserver');

Loader.includeLanguage('site');
Loader.includeLanguage('goals');

document.addLoader(function () {
    if (document.body.id != 'demo-page') {
        return;
    }
    
    window.Goals = new DemoApi;
    
    Shadow.init();
    if (ENV.user) {
        ENV.user.valid = true;
    } else {
        ENV.user = {
            valid: true
        };
    }

    Goals.Tabs = new GoalsTabs('goals-tabs', Data.tabs);
    document._toHideOnClick.push(Goals.Tabs);
    ID('goals-tabs').replace(Goals.Tabs.element);
    if (Goals.Tabs.getNode(ENV.tab)) {
        Goals.Tabs.select(Goals.Tabs.getNode(ENV.tab));
    }
    
    window.Tabs = new DemoTabsApi;
    
    Observer.add('tab-added', function (item) {
        Data.tabs.push(item);
        Goals.Tabs.addNode(item);
    });
    
    Observer.add('tab-edited', function (item) {
        var id = -1;
        foreach(Data.tabs, function (i, tab) {
            if (tab.id == item.id) {
                id = i;
                return false;
            }
        });
        if (id > 0) {
            Data.tabs[id] = item;
        } else {
            Data.tabs.push(item);
        }
        Goals.Tabs.hide();
        Goals.Tabs.editNode(Goals.Tabs.getNode(item.id), item);
    });
    
    Observer.add('tab-removed', function (id) {
        Goals.Tabs.removeNode(Goals.Tabs.getNode(id));
        window.location.hash = '';
    });
    
    Observer.add(Goals.Tabs.EVENT_SORTED, function () {
        Tabs.sort();
    });

    Goals.Add = new GoalsPageAdd;
    Goals.Edit = new GoalsPageEdit;
    Goals.Table = new GoalsWeek;
    Goals.Chart = new GoalsChart;
    Goals.Comment = new GoalsComment;
    Goals.Number = new GoalsNumber;
    Goals.Time = new GoalsTime;
    
    Goals.Table.build();
    
    ID('frame').replace(Goals.Table.frame);
    Goals.Table.frame.id = 'frame';
    Goals.Table.table.id = 'data';

    AnchorObserver.add('(?:(?:tab-(\\d+))|(all-goals))?', function (id, all_goals) {
        var tab = null;
        if (all_goals || !ENV.user.valid) {
            ENV.tab = 0;
        } else if (id) {
            ENV.tab = id;
            tab = Goals.Tabs.getNode(id);
            if (!tab) {
                ENV.tab = 0;
            }
        } else {
            tab = Goals.Tabs.element.firstChild;
            if (tab) {
                ENV.tab = tab.data.id;
            } else {
                ENV.tab = 0;
            }
        }
        Progress.load('Loading tab');
        if (tab) {
            Goals.Tabs.select(tab);
            ID('all-goals').removeClass('selected');
        } else {
            Goals.Tabs.unselect();
            ID('all-goals').addClass('selected');
        }
        Goals.Table.removeGoals();
        foreach(Data.goals, function (i, goal) {
            if (!ENV.tab || goal.tab == ENV.tab) {
                Goals.Table.addGoal(goal);
            }
        });
        Progress.done('Done', true);
    });
    AnchorObserver.start();
    
    Observer.add('goal-added', function (item) {
        Data.goals.push(item);
        Goals.Table.addGoal(item);
        Goals.Add.hide();
    });
    
    Observer.add('goal-edited', function (item) {
        var ind = -1;
        foreach(Data.goals, function (i, goal) {
            if (goal.id == item.id) {
                ind = i;
                return false;
            }
        });
        var moved = item.tab !== undefined && item.tab != ENV.tab;
        var archived = !!item.archived;
        if (!archived) {
            if (ind >= 0) {
            	foreach(item, function (i, value) {
                    Data.goals[ind][i] = item[i];
            	});
            } else {
                Data.goals.push(item);
            }
        }
        if (moved || archived) {
            Goals.Table.removeGoal(item.id);
        } else {
            Goals.Table.editGoal(item);
        }
        if (archived) {
            Data.goals.splice(ind, 1);
        }
        Goals.Edit.hide();
    });
    
    Observer.add('goals-sorted', function (item) {
        foreach(Data.goals, function (i, goal) {
            foreach(item, function (j, data) {
                if (goal.id == data.id) {
                    Data.goals[i].position = data.position;
                    return false;
                }
            });
        });
    });
    
    Observer.add('goal-removed', function (id) {
        var ind = -1;
        foreach(Data.goals, function (i, goal) {
            if (goal.id == id) {
                ind = i;
                return false;
            }
        });
        Data.goals.splice(ind, 1);
        
        Goals.Table.removeGoal(id);
        Goals.Edit.hide();
    });
    
    Observer.add('form-submitted', function (data) {
        if (!data.id) {
            data.position = Goals.Table.table.tBodies[0].childNodes.length;
            Goals.add(data);
        } else {
            Goals.edit(data.id, data);
        }
    });
    
    Observer.add(Goals.Add.EVENT_SET, function () {
        ID('goals-table').addClass('hidden');
        Goals.Edit.hide(true);
    });
    
    Observer.add(Goals.Edit.EVENT_SET, function () {
        ID('goals-table').addClass('hidden');
        Goals.Add.hide(true);
    });
    
    Observer.add(Goals.Add.EVENT_HIDDEN, function () {
        ID('goals-table').removeClass('hidden');
    });
    
    Observer.add(Goals.Edit.EVENT_HIDDEN, function () {
        ID('goals-table').removeClass('hidden');
    });
});