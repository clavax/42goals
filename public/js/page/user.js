include('utils.common');
include('utils.site');
include('mohawk.utils.Date');
include('chart.ChartFactory');
include('UI.ProfileLayout');
include('UI.ProfileGoals');
include('UI.Comments');

include('interface.GoalsApi');
include('UI.MeGoals');

document.addLoader(function () {
    if (document.body.id != 'users-page') {
        return;
    }
    
    Data.layout = [
        {
            id: 1
        },
        {
            id: 2
        },
        {
            id: 3,
            size: 'wide'
        }
    ];
    
    foreach(Data.layout, function (i, layout) {
        foreach(Data.charts, function (j, chart) {
            if (layout.id == chart.position) {
                Data.layout[i].chart = chart;
            }
        });
    });
    
    if (Data.charts && !(Data.charts instanceof Array && !Data.charts.length)) {
        Me.Layout = new ProfileLayout('me-layout', Data.layout);
        ID('me-layout').replace(Me.Layout.element);
        
        Me.Chart = ChartFactory.factory(ENV.chart_type);
        
        foreach(Data.charts, function (i, chart) {
            var node = Me.Layout.getNode(chart.position);
            node.header.setHTML(chart.title);
            
            var w = node.hasClass('wide') ? 662 : 320;
            var h = 240;
            
            Me.Chart.set({
                canvas: node.canvas.id,
                width: w,
                height: h,
                type: chart.type,
                data: Object.values(chart.data),
                interpolate: chart.interpolate * 1
            });
            
            Me.Chart.draw();
        });
    };
});

document.addLoader(function () {
    if (document.body.id != 'users-goals-page') {
        return;
    }
    
    Me.editGoals = function () {
        ID('me-goals-table-wrap').addClass('hidden');
        ID('me-goals-list-wrap').removeClass('hidden');
    };
    
    Me.doneGoals = function () {
        ID('me-goals-table-wrap').removeClass('hidden');
        ID('me-goals-list-wrap').addClass('hidden');
    };
    
    window.GoalsTable = new ProfileGoals('me-goals', Data.goals);
    
    var user_goals = [];
    foreach (Data.goals, function (i, goal) {
        if (goal.user == ENV.UID) {
            user_goals.push(goal);
        }
    });
    window.GoalsList = new MeGoals('me-goals-list', user_goals);
    ID('me-goals').replace(GoalsTable.table);
    ID('me-goals-list').replace(GoalsList.element);
    ID('me-goals-list-wrap').addClass('hidden');
    
    Observer.add('profile-goals-edit-click', Me.editGoals);
        
    window.Goals = new GoalsApi;
    
    Observer.add('goal-edited', function (item) {
        var node = GoalsList.getNode(item.id);
        node.replaceClass('loading', item.privacy);
        
        var row = ID('goal-' + item.id);
        if (item.privacy == 'private') {
            row.addClass('hidden');
        } else {
            row.removeClass('hidden');
        }
    });
});
