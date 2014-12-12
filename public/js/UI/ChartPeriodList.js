include('mohawk.UI.SelectableList');

window.ChartPeriodList = Mohawk.UI.SelectableList.extend({
    selected: [],
    multiple: false,
    
    __construct: function (id) {
        var period_list = [
            {
                id: 'week',
                title: LNG.week
            },
            {
                id: 'month',
                title: LNG.month
            },
            {
                id: 'quarter',
                title: LNG.quarter
            },
            {
                id: 'year',
                title: LNG.year
            },
            {
                id: 'all',
                title: LNG.all_time
            }
        ];
        parent.__construct(id, period_list);
        self.element.addClass('options');
    }
});