Mohawk.UI.InputTime = new Class({
    strict: true,
    display_seconds: false,
    className: 'time',

    __construct: function (input, display_seconds, strict) {
        self.strict = !!strict;
        self.display_seconds = !!display_seconds;
        
        if (!IE) {
            input.setAttribute('type', 'hidden');
        } else {
            input.style.display = 'none';
        }
        var time = self.parse(input.value);
    
        var container = document.createElement('FIELDSET');
        container.className = self.className;
        if (input.nextSibling) {
            input.parentNode.insertBefore(container, input.nextSibling);
        } else {
            input.parentNode.appendChild(container);
        }
    
        var hour = FormsInterface.createInput('text', 'hour_of_' + input.name, time.hour);
        hour.disabled = input.disabled;
        hour.size = 2;
        hour.maxLength = 2;
        container.appendChild(hour);
        container.appendChild(document.createTextNode(':'));

        var mins = FormsInterface.createInput('text', 'mins_of_' + input.name, time.mins);
        mins.disabled = input.disabled;
        mins.size = 2;
        mins.maxLength = 2;
        container.appendChild(mins);
        if (self.display_seconds) {
            container.appendChild(document.createTextNode(':'));
        }
    
        var secs = FormsInterface.createInput('text', 'secs_of_' + input.name, time.secs);
        secs.disabled = input.disabled;
        secs.size = 2;
        secs.maxLength = 2;
        container.appendChild(secs);
        if (!display_seconds) {
            secs.collapse();        
        }
    
        hour.onchange = mins.onchange = secs.onchange = function () {
        	if (hour.value.length || mins.value.length) {
        		input.value = self.format(hour.value, mins.value, secs.value);
        	} else {
        		input.value = '';
        	}
        };
        input.onchange = function () {
            var time = self.parse(input.value);
            hour.value = time.hour;
            mins.value = time.mins;
            secs.value = time.secs;
        };
        if (hour.value.length || mins.value.length || secs.value.length) {
        	input.value = self.format(hour.value, mins.value, secs.value);
        }
    },
    
    format: function (hour, mins, secs) {
        if (self.strict) {
            var hour = hour.toString().pad(2, '0');
            var mins = mins.toString().pad(2, '0');
            if (self.display_seconds) {
                var secs = secs.toString().pad(2, '0');
            }
        }
    
        return hour + ':' + mins + (self.display_seconds ? ':' + secs : '');
    },
    
    parse: function (str) {
        var time_regexp = new RegExp('^(\\d{1,2})[\.-:](\\d{1,2})(?:[\.-:](\\d{1,2}))?$');
        
        var hour = '', mins = '', secs = '';
        
        if (time = time_regexp.exec(str)) {
            hour = time[1];
            mins = time[2];
            secs = time[3];
        } else {
            if (self.strict) {
                hour = '00';
                mins = '00';
                secs = '00';
            }
        }
        return {hour: hour, mins: mins, secs: secs};
    }
});

Mohawk.UI.InputTime.set = function (input, display_seconds, strict) {
    return new Mohawk.UI.InputTime(input, display_seconds, strict);
};