Mohawk.UI.SelectDate = new Singletone({
    className: 'select-date',
    minYear: (new Date()).getFullYear() - 5,
    maxYear: (new Date()).getFullYear() + 5,
    
    format: function (day, month, year) {
        if (day.toString().length == 1) {
            day = '0' + day;
        }
    
        if (month.toString().length == 1) {
            month = '0' + month;
        }
    
        if (year.toString().length == 2) {
            year = ((parseInt(year) < 50 ) ? '20' : '19') + year;
        }
    
        //return day + '/' + month + '/' + year;
        return year + '-' + month + '-' + day;
    },
    
    parse: function (str) {
        var date_regexp = new RegExp('([0-9]{1,4})[\.\/-]([0-9]{1,2})[\.\/-]([0-9]{2}|\d{2})');
        if (date = date_regexp.exec(str)) {
            var year  = parseInt(date[1]);
            var month = date[2] * 1;
            var day   = date[3] * 1;
            year = (year.toString().length == 2) ? (year < 50 ? '20' : '19').toString() + year : year;
        } else {
            var today = new Date;
            var day   = today.getDate();
            var month = today.getMonth() + 1;
            var year  = today.getFullYear();
        }
        return {day: day, month: month, year: year};
    },
    
    set: function (input) {
        if (!IE) {
            input.setAttribute('type', 'hidden');
        } else {
            input.style.display = 'none';
        }
        var date = self.parse(input.value);
    
        var container = document.createElement('FIELDSET');
        container.className = self.className;
        if (input.nextSibling) {
            input.parentNode.insertBefore(container, input.nextSibling);
        } else {
            input.parentNode.appendChild(container);
        }
    
        var days = {};
        for (var i = 1; i <= 31; i ++) {
            days[i] = i;
        }
        var months = {};
        for (var i = 1; i <= 12; i ++) {
            months[i] = i;
        }
        var years = [];
        for (var i = self.minYear; i <= self.maxYear; i ++) {
            years[i] = i;
        }

        var day = FormsInterface.createInput('select', 'day_of_' + input.name, date.day, days);
        day.disabled = input.disabled;
        container.appendChild(day);
        container.appendChild(document.createTextNode(' '));
        
        var month = FormsInterface.createInput('select', 'month_of_' + input.name, date.month, months);
        month.disabled = input.disabled;
        container.appendChild(month);
        container.appendChild(document.createTextNode(' '));
        
        var year = FormsInterface.createInput('select', 'year_of_' + input.name, date.year, years);
        year.disabled = input.disabled;
        container.appendChild(year);
    
        day.onchange = month.onchange = year.onchange = function () {
            input.value = self.format(day.value, month.value, year.value);
        };
        input.onchange = function () {
            var date = self.parse(input.value);
            day.value = date.day;
            month.value = date.month;
            year.value = date.year;
        }
        input.value = self.format(day.value, month.value, year.value);
    }
});