include('mohawk.UI.SelectableList');

window.ChartTypeList = Mohawk.UI.SelectableList.extend({
    selected: [],
    multiple: false,
    
    __construct: function (id) {
        var type_list = [
            {
                id: 'column',
                title: LNG.chart_column
            },
            /*{
                id: 'bar',
                title: LNG.chart_bar
            },*/
            {
                id: 'line',
                title: LNG.chart_line
            },
            {
                id: 'area',
                title: LNG.chart_area
            },
            {
                id: 'pie',
                title: LNG.chart_pie
            }
        ];
        parent.__construct(id, type_list);
        self.element.addClass('options');
    },
    
    createNode: function (data) {
        var node = parent.createNode(data);
        node.setHTML('<img src="' + URL.img + 'site/chart_' + data.id + '.png" alt="' + data.title + '" />');
        return node;
    }
});