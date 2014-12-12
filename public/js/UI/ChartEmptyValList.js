include('mohawk.UI.SelectableList');

window.ChartEmptyValList = Mohawk.UI.SelectableList.extend({
    selected: [],
    multiple: false,
    
    __construct: function (id) {
        var options_list = [
            {
                id: 'zero',
                title: LNG.emptyval_zero
            },
            {
                id: 'last',
                title: LNG.emptyval_last
            }
        ];
        parent.__construct(id, options_list);
        self.element.addClass('options');
    }
});