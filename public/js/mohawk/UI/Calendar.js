include('mohawk.utils.Date');
// Mohawk.Loader.addCss('calendar.css');
Mohawk.Loader.includeLanguage('calendar');

Mohawk.UI.Calendar = new Class({
    table: null,
    month: 0,
    year: 0,
    
    __construct: function () {
        var today = new Date(); 
        self.table = self.buildMonth(today.getFullYear(), today.getMonth() + 1);
    },
    
    buildMonth: function (year, month) {
        var today = new Date();
        self.month = month;
        self.year = year;
        self.today = today;
        self.months = [
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
        
        var table = document.createElement('TABLE');
        table.onclick = function (event) {
            event = Mohawk.DOM.event(event);
            event.stopPropagation();
            return false;
        };
        
        //calendar caption
        var caption = self.createCaption();
        table.appendChild(caption);
        
        //calendar head
        var thead = self.createTHead();
        table.appendChild(thead);
        
        // calendar days
        var tbody = self.createTBody();
        table.appendChild(tbody);

        return table;
    },
    
    createCaption: function (year, month) {
        var caption = document.createElement('CAPTION');
        caption.setHTML(self.months[self.month - 1].name + ' ' + self.year);
        return caption;
    },
    
    createTHead: function () {
        var thead = document.createElement('THEAD');
        var thead_row = document.createElement('TR');
        thead.appendChild(thead_row);
        for (var i = 0; i < static.weekdays.length; i ++) {
            var cell = document.createElement('TH');
            cell.setHTML(static.weekdays[i].name);
            cell.addClass(static.weekdays[i].holiday ? 'holiday' : '');
            thead_row.appendChild(cell);
        }
        return thead;
    },
    
    createTBody: function () {
        var tbody = document.createElement('TBODY');
        
        var day_of_week   = Date.getWeekday(1, self.month, self.year);
        var days_in_month = self.months[self.month - 1].days;
        
         //calendar body
        var row = document.createElement('TR');
        tbody.appendChild(row);
        for (var i = 1; i < day_of_week; i ++) {
            var cell = document.createElement('TD');
            row.appendChild(cell);
        }
        var i = 1;
        for (; i <= days_in_month; i ++) {
            if ((i + day_of_week - 1) % 7 == 1) {
                row = document.createElement('TR');
                tbody.appendChild(row);
            }
            var date = new Date(self.year, self.month - 1, i);
            var cell = self.createCell(date);
            
            row.appendChild(cell);
        }
        i = (days_in_month + day_of_week - 1) % 7;
        if (!i) {
            i = 7;
        }
        for (; i < 7; i ++) {
            var cell = document.createElement('TD');
            row.appendChild(cell);
        }
        
        return tbody;
    },
    
    createCell: function (date) {
        var cell  = document.createElement('TD');
        cell.data = {date: date};

        if (date.getDate() == self.today.getDate() && date.getMonth() == self.today.getMonth() && date.getFullYear() == self.today.getFullYear()) {
            cell.addClass('today');
        }
        
        cell.onmouseover = function () {
            cell.addClass('over');
        };
        
        cell.onmouseout = function () {
            cell.removeClass('over');
        };
        
        cell.innerHTML = date.getDate();

        return cell;
    },
    
    setDate: function (year, month) {
        if (year instanceof Date) {
            month = year.getMonth() + 1;
            year = year.getFullYear();
        }
        var table = self.buildMonth(year, month);
        if (self.table.parentNode) {
            self.table.replace(table);
        } else {
            self.table = null;
        }
        self.table = table;
    }
});

Mohawk.UI.Calendar.weekdays = [
    {name: LNG.mo, holiday: false},
    {name: LNG.tu, holiday: false},
    {name: LNG.we, holiday: false},
    {name: LNG.th, holiday: false},
    {name: LNG.fr, holiday: false},
    {name: LNG.sa, holiday: true},
    {name: LNG.su, holiday: true}
];