include('UI.ChartPeriodList');
include('UI.ChartGroupList');
include('UI.ChartTypeList');
include('chart.ChartFactory');

Loader.includeTemplate('chart-constructor');

window.ChartConstructor = new Class({
    __construct: function () {
        self.container = DOM.element('div', {
            id: 'chart-constructor-container',
            html: Template.transform(CHART_CONSTRUCTOR)
        });
        
        self.Period = new ChartPeriodList('chart-period');
        self.container.getElementById('chart-period').replace(self.Period.element);
        
        self.GroupBy = new ChartGroupList('chart-groupby');
        self.container.getElementById('chart-groupby').replace(self.GroupBy.element);
        
        self.Type = new ChartTypeList('chart-type');
        self.container.getElementById('chart-type').replace(self.Type.element);

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
    },
    
    set: function (element) {
        Shadow.show();
        document.body.appendChild(self.container);
        Dragdrop.bringToFront(self.container);
        
        self.element = element;
        self.canvas = ID('chart-canvas');
        self.canvas.style.width = (element.data.size == 'wide' ? '662' : '320') + 'px';
        self.canvas.style.height = '240px';
        
        if (element.data.chart) {
            ID('chart-goal').value = element.data.chart.goal;
            ID('chart-title').value = element.data.chart.title;
            
            self.GroupBy.setSelected(element.data.chart.groupby);
            self.Period.setSelected(element.data.chart.period);
            self.Type.setSelected(element.data.chart.type);
            
            ID('chart-accumulate').checked = element.data.chart.accumulate;
            ID('chart-interpolate').checked = element.data.chart.interpolate;
            ID('chart-fill-empty').checked = element.data.chart.fill_empty;
        }
        
        self.draw();
    },
    
    hide: function () {
        Shadow.hide();
        self.container.remove();
    },
    
    draw: function () {
        self.goal = ChartData.getGoalById(ID('chart-goal').value);
        self.group = self.GroupBy.getSelected() || 'day';
        self.period = self.Period.getSelected() || 'week';
            
        ChartData.setGoal(self.goal);
        ChartData.setGroup(self.group);
        ChartData.setPeriod(self.period);
        
        self.accumulate = ID('chart-accumulate').checked;
        self.interpolate = ID('chart-interpolate').checked;
        self.fill_empty = ID('chart-fill-empty').checked;
        
        ChartData.take_last_value = self.fill_empty;

        self.type = self.Type.getSelected() || 'column';
        
        var data;
        if (self.type == 'pie') {
            data = ChartData.getBooleanData();
        } else {
            data = self.accumulate ? ChartData.getAccumulatedData() : ChartData.getGroupedData()
        }

        Me.Chart.set({
            canvas: self.canvas.id,
            width: self.canvas.offsetWidth,
            height: self.canvas.offsetHeight,
            type: self.type,
            interpolate: self.interpolate,
            data: data
        });
        Me.Chart.draw();
    },
    
    apply: function () {
        var data = {
            goal: self.goal.id,
            title: ID('chart-title').value == LNG.Set_title ? self.goal.title : ID('chart-title').value,
            position: self.element.data.id,
            type: self.type,
            period: self.period,
            groupby: self.group,
            accumulate: self.accumulate ? 1 : 0,
            interpolate: self.interpolate ? 1 : 0,
            fill_empty: self.fill_empty ? 1 : 0
        };
        
        if (self.element.data.chart) {
            Charts.edit(self.element.data.chart.id, data);
        } else {
            Charts.add(data);
        }
        
        self.hide();
    }
});