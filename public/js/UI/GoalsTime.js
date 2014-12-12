include('UI.GoalsNumber');
Loader.includeTemplate('time-example');

window.GoalsTime = GoalsNumber.extend({
    __construct: function () {
        parent.__construct();
        Template.assign('time', Format.date(new Date(), 'H:i'));
        var ex = DOM.element('DIV', {
            innerHTML: Template.transform(TIME_EXAMPLE),
            appendTo: self.element
        });
        self.element.id = 'goals-time';
        self.element.addClass('goals-time-input');
    },
    
    save: function () {
        var value = parseFloat(self.cell.value);
        if (isNaN(value)) {
            value = 0;
        }
        var input = self.toSeconds(self.input.value);
        
        if (self.mode.innerHTML == '+') {
            value += input;
        } else if (self.mode.innerHTML == '-') {
            value -= input;
        } else {
            value = input;
        }
        
        value = Math.round(value, 5);
        self.cell.setValue(value);
        Goals.save(self.cell.row.data.id, Format.date(self.cell.col.data.date, 'Y-m-d'), {value: value});
    },

    toSeconds: function (str) {
        var hms_regexp = /^(\d{1,3})[\.\-:](\d{1,2})[\.\-:](\d{1,2})$/;
        var hm_regexp  = /^(\d{1,3})[\.\-:](\d{1,2})$/;
        var m_regexp   = /^(\d{1,2})$/;
        
        var hour = 0, mins = 0, secs = 0;
        
        if (time = hms_regexp.exec(str)) {
            hour = time[1];
            mins = time[2];
            secs = time[3];
        } else if (time = hm_regexp.exec(str)) {
            hour = time[1];
            mins = time[2];
            secs = 0;
        } else if (time = m_regexp.exec(str)) {
            hour = 0;
            mins = time[1];
            secs = 0;
        } else {
            return;
        }
        
        hour *= 1;
        mins *= 1;
        secs *= 1;
        
        return hour * 3600 + mins * 60 + secs;        
    }
});