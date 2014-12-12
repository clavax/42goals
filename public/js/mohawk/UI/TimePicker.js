Mohawk.Loader.addCss('calendar.css');

Mohawk.UI.TimePicker = new Class({
    display_seconds: false,
    className: 'time',

    __construct: function (input, display_seconds) {
        self.display_seconds = !!display_seconds;
        self.input = input;
        input.onfocus = function () {
            self.show();
        };
        if (input.onchange) {
            input._onchange = input.onchange;
        }
        input.onchange = function () {
        	if (input.value.length) {
	            var time = self.parse(input.value);
	            input.value = self.format(time.hour, time.mins, time.secs);
        	} else {
        		input.value = '';
        	}
            if (typeof(input._onchange) != 'undefined' && input._onchange instanceof Function) {
                input._onchange.call(input);
            }
        };
        input.onclick = function (event) {
            event = Mohawk.DOM.event(event);
            event.stopPropagation();
            return false;
        };
    },
    
    show: function () {
        if (document._ObjectToHide && document._ObjectToHide != self) {
            document._ObjectToHide.doc_hide();
        }
        document._ObjectToHide = self;

        var list = document.createElement('UL');
        list.onclick = function (event) {
            event = Mohawk.DOM.event(event);
            event.stopPropagation();
            return false;
        };
        list.addClass('time-picker');
        for (var h = 0; h <= 23; h ++) {
            for (var m = 0; m < 60; m += 30) {
                var time = self.format(h, m);
                var item = document.createElement('LI');
                item.time = time;
                item.setHTML(time);
                item.onmouseover = function () {
                    this.addClass('over');
                };
                item.onmouseout = function () {
                    this.removeClass('over');
                };
                item.onmousedown = function () {
                    self.input.value = this.time;
                    if (typeof(self.input._onchange) != 'undefined' && self.input._onchange instanceof Function) {
                        self.input._onchange.call(self.input);
                    }
                    self.hide();
                };
                
                list.appendChild(item);
            }
        }
        list.style.left = self.input.coordinates().x + 'px';
        list.style.top = self.input.coordinates().y + self.input.offsetHeight + 'px';
        document.body.appendChild(list);
        Dragdrop.bringToFront(list);
        
        document.addEvent('click', self.doc_hide);
        
        self.element = list;
    },

    doc_hide: function () {
        document._ObjectToHide.hide();
        document.removeEvent('click', document._ObjectToHide.doc_hide);
    },
    
    hide: function () {
        if (self.element) {
            self.element.remove();
        }
    },

    format: function (hour, mins, secs) {
        var hour = hour.toString().pad(2, '0');
        var mins = mins.toString().pad(2, '0');
        if (self.display_seconds) {
            var secs = secs.toString().pad(2, '0');
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
            hour = '00';
            mins = '00';
            secs = '00';
        }
        return {hour: hour, mins: mins, secs: secs};
    }
});

Mohawk.UI.TimePicker.set = function (input, display_seconds) {
    return new Mohawk.UI.TimePicker(input, display_seconds);
};