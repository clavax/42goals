Loader.includeTemplate('mobile-number');

window.MobileNumber = new Class({
    __construct: function () {
        var element = DOM.element('DIV');
        element.addClass('hidden');
        self.element = element;
        
        element.setHTML(Template.transform(MOBILE_NUMBER));
        
        self.input = self.element.getElementsByClassName('mobile-number-input')[0];
        self.mode_indicator = self.element.getElementsByClassName('mode')[0];
    },
    
    inp: function (str) {
        var value = self.input.value;
        value += str;
        self.input.value = value;
    },
    
    dec: function (str) {
        var value = self.input.value;
        if (value.indexOf('.') == -1) {
            if (!value.length) {
                value = 0;
            }
            value += '.';
        }
        self.input.value = value;
    },
    
    sign: function () {
        var value = self.input.value;
        value = -value;
        self.input.value = value;
    },
    
    backspace: function () {
        var value = self.input.value;
        value = value.substr(0, value.length - 1);
        self.input.value = value;
    },
    
    save: function () {
        self.hide();
        
        var input_value = parseFloat(self.input.value);
        if (isNaN(input_value)) {
            return;
        }
        var goal_value = parseFloat(Data.goals[self.goal_id].data.value || 0);
        var total_value = 0;
        
        if (self.mode_indicator.innerHTML == '+') {
            total_value = goal_value + input_value;
        } else if (self.mode_indicator.innerHTML == '-') {
            total_value = goal_value - input_value;
        } else {
            total_value = input_value;
        }
        
        Mobile.setValue(self.goal_id, total_value);
        Data.goals[self.goal_id].data.value = total_value;
        Mobile.save(self.goal_id);
    },
    
    clear: function () {
        self.hide();
        Mobile.setValue(self.goal_id, '');
        Data.goals[self.goal_id].data.value = '';
        Mobile.save(self.goal_id);
    },
    
    show: function (id) {
        self.goal_id = id;
        self.input.value = '';
        self.mode_indicator.innerHTML = '+';
        Shadow.show();
        self.element.removeClass('hidden');
        Dragdrop.bringToFront(self.element);
    },
    
    mode: function () {
        if (self.mode_indicator.innerHTML == '+') {
            self.mode_indicator.innerHTML = '=';
        } else if (self.mode_indicator.innerHTML == '=') {
            self.mode_indicator.innerHTML = '-';
        } else {
            self.mode_indicator.innerHTML = '+';
        }
    },
    
    hide: function () {
        Shadow.hide();
        self.element.addClass('hidden');
    }
});