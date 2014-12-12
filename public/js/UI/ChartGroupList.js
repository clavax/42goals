include('mohawk.UI.SelectableList');

window.ChartGroupList = Mohawk.UI.SelectableList.extend({
    selected: [],
    multiple: false,
    
    __construct: function (id) {
        var groupby_list = [
            {
                id: 'day',
                title: LNG.day
            },
            {
                id: 'week',
                title: LNG.week
            },
            {
                id: 'weekday',
                title: LNG.week_days
            },
            {
                id: 'month',
                title: LNG.month
            }
        ];
        parent.__construct(id, groupby_list);
        self.element.addClass('options');
    }
});