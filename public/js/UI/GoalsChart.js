include('UI.ChartPeriodList');
include('UI.ChartGroupList');
include('UI.ChartTypeList');
include('chart.ChartFactory');
Loader.includeTemplate('share-default');
Loader.includeTemplate('share-result');

window.GoalsChart = new Class({
    initiated: false,
    
    init: function () {
        self.initiated = true;

        self.canvas = ID('chart-canvas');
        self.table  = ID('chart-table');
        self.image  = ID('chart-image');
        
        self.Period = new ChartPeriodList('chart-period');
        ID('chart-period').replace(self.Period.element);
        
        self.GroupBy = new ChartGroupList('chart-groupby');
        ID('chart-groupby').replace(self.GroupBy.element);
        
        self.Type = new ChartTypeList('chart-type');
        ID('chart-type').replace(self.Type.element);
        
        Observer.add(self.Type.EVENT_SELECTED, function () {
            var type = self.Type.selected[0].data.id;
            switch (type) {
            case 'pie':
                self.GroupBy.element.parentNode.addClass('hidden');
                ID('chart-advanced').parentNode.addClass('hidden');
                break;
            default:
                self.GroupBy.element.parentNode.removeClass('hidden');
                ID('chart-advanced').parentNode.removeClass('hidden');
                if (self.goal.type == 'boolean') {
                    self.Period.getNode('week').addClass('hidden');
                    self.GroupBy.getNode('day').addClass('hidden');
                } else {
                    self.Period.getNode('week').removeClass('hidden');
                    self.GroupBy.getNode('day').removeClass('hidden');
                }
                break;
            }
            self.draw();
        });
        
        Observer.add(self.Period.EVENT_SELECTED, function () {
            self.draw();
        });
        
        Observer.add(self.GroupBy.EVENT_SELECTED, function () {
            self.draw();
        });
        
        self.Chart = ChartFactory.factory(ENV.chart_type);
    },
    
    draw: function (goal) {
        if (goal) {
            self.goal = Object.clone(goal);
            
            switch (goal.type) {
            case 'boolean':
                self.Type.getNode('pie').removeClass('hidden');
                self.Type.setSelected('pie');
                self.Period.setSelected('month');
                self.GroupBy.setSelected('week');
                break;
            default:
                self.Type.getNode('pie').addClass('hidden');
                self.Type.setSelected('column');
                self.Period.setSelected('month');
                self.GroupBy.setSelected('day');
                break;
            }
            
            Observer.fire(self.Type.EVENT_SELECTED);
        } else {
            goal = self.goal;
        }

        var type = self.Type.getSelected() || 'column';
        
        var data;
        ChartData.setGoal(goal);
        ChartData.setGroup(self.GroupBy.getSelected() || 'day');
        ChartData.setPeriod(self.Period.getSelected() || 'none');
        ChartData.take_last_value = ID('chart-fill-empty').checked;
        
        var accumulate = ID('chart-accumulate').checked;
        if (type == 'pie') {
            data = ChartData.getBooleanData();
        } else {
            data = accumulate ? ChartData.getAccumulatedData() : ChartData.getGroupedData();
        }
        self.Chart.set({
            canvas: self.canvas.id,
            width: 800,
            height: 300,
            type: type,
            interpolate: ID('chart-interpolate').checked,
            data: data
        });
        self.Chart.draw();
        
//        self.drawImage(data);
        self.drawTable(data);
    },
    
    drawTable: function (data) {
        var table = new ChartTable('chart-table', {}, {extended: LNG.Date, value: LNG.Value, plan: LNG.Planned});
        table.type = self.goal.type;
        table.setRows(data);
        self.table.replace(table.element);
        self.table = table.element;
    },
    
    
    share: function (post_to) {
        var req = new Ajax(URL.home + 'api/share/');
        req.responseHandler = function (req) {
            var item = req.data.item;
            if (item) {
                var url = URL.host + URL.home + 'p/' + item.uid;
                Template.assign('url', url);
                Template.assign('goal', self.goal);
                Template.assign('code', '<a href="' + url + '"><img src="' + url + '.png" alt="My chart for ' + self.goal.title + '" border="0" /></a>');
                ID('chart-share').setHTML(Template.transform(SHARE_RESULT));
            } else {
                // error
            }
        };
        
        var src = Goals.Edit.Chart.image.firstTag('img').src;
        var data = src.substr(src.indexOf('?') + 1);
        req.send({data: data, goal: self.goal.id, post_to: post_to});
        ID('chart-share').setHTML(LNG.Loading);
    },
    
    hide: function () {
        Shadow.hide();
        self.element.addClass('hidden');
    }
});

window.ChartTable = Mohawk.UI.Table.extend({
    createCellValue: function (row, col, data) {
        var cell = parent.createCell(row, col, data);
        
        if (self.type == 'time' || self.type == 'timer') {
            cell.setHTML(Goals.Table.formatTime(data * 3600, '{%h}:{%m}'));
        } else {
            cell.setHTML(Math.round(data, 2));
        }
        
        return cell;
    },
    
    createCellPlan: function (row, col, data) {
        var period = 0;
        if (data instanceof Array) {
            period = data[1];
            data = data[0];
        }
        var cell = parent.createCell(row, col, data);
        
        if (self.type == 'time' || self.type == 'timer') {
            cell.setHTML(Goals.Table.formatTime(data, '{%h}:{%m}'));
        }
        
        if (period > 0) {
            cell.setHTML(cell.innerHTML + ' in ' + period + 'day' + (period > 1 ? 's' : ''));
        }
        
        return cell;
    }
});
