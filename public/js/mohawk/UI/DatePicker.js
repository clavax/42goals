include('mohawk.UI.Calendar');

Mohawk.UI.DatePicker = Mohawk.UI.Calendar.extend({
    input: null,
    date: null,
    
    __construct: function () {
    },
    
    createCaption: function () {
        var caption = parent.createCaption();

        var prev = document.createElement('a');
        prev.addClass('prev');
        prev.setHTML('&larr;');
        prev.href = '#prev-month';
        prev.onclick = function () {
            if (month == 1) {
                month = 12;
                year --;
            } else {
                month --;
            }
            self.showDate(year, month);
            return false;
        };
        
        var next = document.createElement('a');
        next.addClass('next');
        next.setHTML('&rarr;');
        next.href = '#next-month';
        next.onclick = function () {
            if (month == 12) {
                month = 1;
                year ++;
            } else {
                month ++;
            }
            self.showDate(year, month);
            return false;
        };
        
        var b = document.createElement('b');
        b.setHTML(months[month - 1].name + ' ' + year);
        
        caption.removeChildren();
        caption.appendChild(prev);
        caption.appendChild(b);
        caption.appendChild(next);
        
        return caption;
    },
    
    createCell: function (date) {
        var cell = parent.createCell(date);
        
        var cur = Date.parse(self.input.value);
        var cur_date = new Date(cur.year, cur.month - 1, cur.day);
        if (date.toString() == cur_date.toString()) {
            cell.addClass('current');
        }

        cell.onclick = function () {
            self.input.value = cell.data.date.getId();
            if (self.input.onchange instanceof Function) {
                self.input.onchange.apply(self.input);
            }
            self.hide();
        };
        
        cell.innerHTML = day.getDate();

        return cell;
    },
    
    setDate: function (year, month) {
        parent.setDate(year, month);
        self.show();
    },

    set: function (input) {
        self.input = input;
        input._onfocus = input.onfocus;
        input.onfocus = function () {
            if (input._onfocus instanceof Function) {
                input._onfocus.call(input);
            }
            var date = Date.parse(input.value);
            self.setDate(date.year, date.month, date.day);
        };
        input.onclick = function (event) {
            event = Mohawk.DOM.event(event);
            event.stopPropagation();
            return false;
        };
    },
    
    show: function () {
        document._toHideOnClick.push(self);
        
        self.element.style.left = '-1000px';
        self.element.display();
        var left = self.input.coordinates().x;
        var pad  = 20;
        if (left + self.element.offsetWidth > document.size().width - pad) {
            left = document.size().width - pad - self.element.offsetWidth;
        }
        self.element.style.left = left + 'px';
        self.element.style.top = self.input.coordinates().y + self.input.offsetHeight + 'px';
        Dragdrop.bringToFront(self.element);
    },

    hide: function () {
        self.element.collapse();
    },

    append: function (element) {
        (element || document.body).appendChild(self.element);
    }

});

Mohawk.UI.DatePicker.set = function (element) {
    var Calendar = new Mohawk.UI.DatePicker();
    Calendar.set(element);
    return Calendar;
};