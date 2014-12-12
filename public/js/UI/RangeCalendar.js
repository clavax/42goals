include('mohawk.kernel.Dragdrop');
include('mohawk.utils.Date');
//Mohawk.Loader.addCss('calendar.css');
Mohawk.Loader.includeLanguage('calendar');

window.RangeCalendar = new Class({
    element: null,
    startdate: null,
    enddate: null,
    marked: [],
    
    __construct: function (id) {
        self.id = id;

        self.element = DOM.element('DIV');
        self.element.id = id;
        self.element.onclick = DOM.stopEvent;
        
        var today = new Date();
        self.element.appendChild(self.build(today.getFullYear(), today.getMonth() + 1));
        
        self.EVENT_STARTDATE_SET = 'startdate-set';
        self.EVENT_ENDDATE_SET = 'enddate-set';
        
        document._toHideOnClick.push(self);
    },
    
    build: function (year, month) {
        var container = DOM.element('DIV');
        container.addClass('range-calendar');
        
        var list = DOM.element('UL');
        
        var num = 3;

        var prev = DOM.element('a');
        prev.addClass('go', 'prev');
        prev.setHTML('&lsaquo;');
        prev.href = '#prev-month';
        prev.year = year;
        prev.month = month;
        prev.onclick = function () {
            var m = prev.month;
            var y = prev.year;
            if (m == 1) {
                m = 12;
                y --;
            } else {
                m --;
            }
            self.setDate(y, m);
            return false;
        };
        
        var next = DOM.element('a');
        next.addClass('go', 'next');
        next.setHTML('&rsaquo;');
        next.href = '#next-month';
        next.year = year;
        next.month = month;
        next.onclick = function () {
            var m = next.month;
            var y = next.year;
            if (m == 12) {
                m = 1;
                y ++;
            } else {
                m ++;
            }
            self.setDate(y, m);
            return false;
        };
        
        self.marked = [];
        
        var last_table = null;
        for (var i = 0; i < num; i ++) {
            var table = self.buildMonth(year, month, last_table);
            if (!last_table) {
                self.firstTable = table;
            }
            last_table = table;
            var li = DOM.element('LI');
            li.appendChild(table);
            list.appendChild(li);
            
            month ++;
            if (month > 12) {
                month = 1;
                year ++;
            }
        }

        container.appendChild(prev);
        container.appendChild(list);
        container.appendChild(next);
        
        var hint = DOM.element('P');
        hint.setHTML('To select a range: click on a date of the beginning, then click on a date of the end.<br /> To select a single day, simply click on it twice.');
        hint.appendTo(container);
        
        return container;
    },
    
    buildMonth: function (year, month, last_table) {
        var months = [
            {name: LNG.January, days: 31},
            {name: LNG.February, days: Date.isLeapYear(year) ? 29 : 28},
            {name: LNG.March, days: 31},
            {name: LNG.April, days: 30},
            {name: LNG.May, days: 31},
            {name: LNG.June, days: 30},
            {name: LNG.July, days: 31},
            {name: LNG.August, days: 31},
            {name: LNG.September, days: 30},
            {name: LNG.October, days: 31},
            {name: LNG.November, days: 30},
            {name: LNG.December, days: 31}
        ];
        
        var today = new Date();
        
        var table = DOM.element('TABLE');
        table.onclick = function (event) {
            event = Mohawk.DOM.event(event);
            event.stopPropagation();
            return false;
        };
        
        //calendar head
        var caption = DOM.element('CAPTION');
        var title = months[month - 1].name;
        if (month == 1) {
            title += ' ' + year;
        }
        caption.setHTML(title);
        
        table.appendChild(caption);
        
        //calendar head
        var thead = DOM.element('THEAD');
        table.appendChild(thead);
        var thead_row = DOM.element('TR');
        thead.appendChild(thead_row);
        for (var i = 0; i < static.weekdays.length; i ++) {
            var cell = DOM.element('TH');
            cell.setHTML(static.weekdays[i].name);
            cell.addClass(static.weekdays[i].holiday ? 'holiday' : '');
            thead_row.appendChild(cell);
        }
        
        // calendar days
        var tbody = DOM.element('TBODY');
        table.appendChild(tbody);
        
        var day_of_week   = Date.getWeekday(1, month, year);
        var days_in_month = months[month - 1].days;
        
         //calendar body
        var row = DOM.element('TR');
        tbody.appendChild(row);
        for (var i = 1; i < day_of_week; i ++) {
            var cell = DOM.element('TD');
            row.appendChild(cell);
        }
        var i = 1;
        var last_cell = null;
        for (; i <= days_in_month; i ++) {
            if ((i + day_of_week - 1) % 7 == 1) {
                row = DOM.element('TR');
                tbody.appendChild(row);
            }
            var date = new Date(year, month - 1, i);
            var cell = self.createCell(date);
            if (last_cell) {
                last_cell.nextCell = cell;
            } else {
                if (last_table) {
                    last_table.lastCell.nextCell = cell;
                }
                table.firstCell = cell;
            }
            last_cell = cell;

            if (date.toString() == today.toString()) {
                cell.addClass('today');
            }
            row.appendChild(cell);
        }
        table.lastCell = cell;

        i = (days_in_month + day_of_week - 1) % 7;
        if (!i) {
            i = 7;
        }
        for (; i < 7; i ++) {
            var cell = DOM.element('TD');
            row.appendChild(cell);
        }
        return table;
    },
    
    createCell: function (day) {
        var cell  = DOM.element('TD');
        cell.data = {date: day};
        
        cell.onmouseover = function () {
            cell.addClass('over');
        };
        
        cell.onmouseout = function () {
            cell.removeClass('over');
        };
        
        cell.onclick = function () {
            if (!self.startdate) {
                self.startdate = cell.data.date;
                Observer.fire(self.EVENT_STARTDATE_SET, cell.data.date);
            } else if (!self.enddate) {
                if (self.startdate.le(cell.data.date)) {
                    self.enddate = cell.data.date;
                    Observer.fire(self.EVENT_ENDDATE_SET, cell.data.date);
                } else {
                    self.startdate = cell.data.date;
                    Observer.fire(self.EVENT_STARTDATE_SET, cell.data.date);
                }
            } else {
                self.startdate = cell.data.date;
                self.enddate = null;
                Observer.fire(self.EVENT_STARTDATE_SET, cell.data.date);
                Observer.fire(self.EVENT_ENDDATE_SET, null);
            }
            self.markRange();
        };
        
        cell.innerHTML = '<small>' + day.getDate() + '</small>';
        cell.id = self.getId(day);

        return cell;
    },
    
    getId: function (date) {
        return self.id + '-' + (date instanceof Date ? date.getId() : date);
    },
    
    getCell: function (date) {
        return ID(self.getId(date));
    },
    
    markRange: function () {
        foreach(self.marked, function () {
            this.removeClass('marked', 'start', 'end', 'one');
        });
        
        if (!self.startdate) {
            return;
        }
        
        var cell = self.getCell(self.startdate);
        
        if (cell) {
            self.markCell(cell);
            cell.replaceClass('marked', 'start');
        } else {
            if (self.firstTable.firstCell.data.date.gt(self.startdate)) {
                cell = self.firstTable.firstCell;
            } else {
                return;
            }
        }
        
        if (!self.enddate) {
            return;
        }
        
        if (cell.data.date.eq(self.startdate) && cell.data.date.eq(self.enddate)) {
            cell.replaceClass('start', 'one');
            return;
        }
        
        while (cell.nextCell) {
            if (cell.nextCell.data.date.le(self.enddate)) {
                self.markCell(cell.nextCell);
                cell = cell.nextCell;
                if (cell.data.date.eq(self.enddate)) {
                    cell.replaceClass('marked', 'end');
                    break;
                }
            } else {
                break;
            }
        }
    },
    
    markCell: function (cell) {
        cell.addClass('marked');
        self.marked.push(cell);
    },

    setDate: function (year, month) {
        if (year instanceof Date) {
            month = year.getMonth() + 1;
            year = year.getFullYear();
        }
        var container = self.build(year, month);
        self.element.setChild(container);
        self.markRange();
    },

    hide: function () {
        self.element.remove();
    }    
});

RangeCalendar.weekdays = [
    {name: LNG.mo, holiday: false},
    {name: LNG.tu, holiday: false},
    {name: LNG.we, holiday: false},
    {name: LNG.th, holiday: false},
    {name: LNG.fr, holiday: false},
    {name: LNG.sa, holiday: true},
    {name: LNG.su, holiday: true}
];