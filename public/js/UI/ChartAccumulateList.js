include('mohawk.UI.SelectableList');

window.ChartAccumulateList = Mohawk.UI.SelectableList.extend({
    selected: [],
    multiple: false,
    
    __construct: function (id) {
        var options_list = [
            {
                id: 'no',
                title: LNG.accumulate_no
            },
            {
                id: 'yes',
                title: LNG.accumulate_yes
            }
        ];
        parent.__construct(id, options_list);
        self.element.addClass('options');
    }
});