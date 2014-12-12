include('mohawk.UI.Calendar');

window.WeekPick = Mohawk.UI.Calendar.extend({
    buildMonth: function (year, month) {
        var table = parent.buildMonth(year, month);
        table.addClass('calendar', 'rounded', 'shadowed');
        document._toHideOnClick.push(self);
        return table;
    },
    
    createCaption: function (year, month) {
        var caption = document.createElement('CAPTION');
        caption.setHTML(self.months[self.month - 1].name + ' ' + self.year);
        
        var prev = document.createElement('a');
        prev.addClass('prev');
        prev.setHTML('&larr;');
        prev.href = '#prev-month';
        prev.onclick = function () {
            var month = self.month;
            var year = self.year;
            if (month == 1) {
                month = 12;
                year --;
            } else {
                month --;
            }
            self.setDate(year, month);
            return false;
        };
        
        var next = document.createElement('a');
        next.addClass('next');
        next.setHTML('&rarr;');
        next.href = '#next-month';
        next.onclick = function () {
            var month = self.month;
            var year = self.year;
            if (month == 12) {
                month = 1;
                year ++;
            } else {
                month ++;
            }
            self.setDate(year, month);
            return false;
        };
        caption.appendChild(prev);
        caption.appendChild(next);
        
        return caption;
    },    
    
    createCell: function (date) {
        var cell = parent.createCell(date);
        
        cell.onclick = function () {
            Goals.Table.moveTo(date.addDay(1 - (date.getDay() || 7)));
            self.hide();
        };
        
        return cell;
    },
    
    hide: function () {
        self.table.remove();
    }
});