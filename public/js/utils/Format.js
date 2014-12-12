window.Format = new Singletone({
    months_abbr: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    weekdays_abbr: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    weekdays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
    
    date: function (date, format) {
        if (!(date instanceof Date)) {
            date = Date.fromString(date);
        }

        var D = {
            j: date.getDate(),
            w: date.getDay(),
            n: date.getMonth() + 1,
            Y: date.getFullYear(),
            H: date.getHours(),
            i: date.getMinutes(),
            s: date.getSeconds()
        };
        D.d = D.j.toString().pad(2, '0');
        D.m = D.n.toString().pad(2, '0');
        D.H = D.H.toString().pad(2, '0');
        D.i = D.i.toString().pad(2, '0');
        D.s = D.s.toString().pad(2, '0');
        
        D.F = LNG['of_' + self.months[D.m - 1]] || self.months[D.m - 1];
        D.M = LNG[self.months_abbr[D.m - 1]] || self.months_abbr[D.m - 1];
        D.l = LNG[self.weekdays[D.w]] || self.weekdays[D.w];
        D.D = LNG[self.weekdays_abbr[D.w]] || self.weekdays_abbr[D.w];

        if (ENV.language == 'ru') {
            D.l = D.l.toLowerCase();
            D.F = D.F.toLowerCase();
        }

        var formatted = '';
        for (var i = 0; i < format.length; i ++) {
            var chr = format.substr(i, 1);
            formatted = formatted.concat(D[chr] != undefined ? D[chr] : chr);
        }
        
        return formatted;
    },
    
    dateRange: function (from, to, sep, format) {
        var range = '';
        if (!sep) {
            sep = ' - ';
        }
        if (!format) {
            format = {
                d: 'j',
                m: 'M'
            };
        }
        if (!format.dm) {
            format.dm = ENV.language == 'ru' ? format.d + ' ' + format.m : format.m + ' ' + format.d;
        }
        if (from.getMonth() == to.getMonth()) {
            if (from.getDate() == to.getDate()) {
                range = Format.date(from, format.dm);
            } else {
                if (ENV.language == 'ru') {
                    range =  Format.date(from,  format.d) + sep + Format.date(to, format.d) + ' ' + Format.date(from,  format.m);    
                } else {
                    range = Format.date(from,  format.dm) + sep + Format.date(to, format.d);
                }
            }
        } else {
            range = Format.date(from, format.dm) + sep + Format.date(to, format.dm);
        }
        
        return range;
    },
    
    humanDate: function (date, today) {
        if (!(date instanceof Date)) {
            date = Date.fromString(date);
        }
        
        if (today == undefined) {
            today = new Date();
        }
        var human_date = '';
        if (today.getFullYear() == date.getFullYear()) {
            if (today.getMonth() == date.getMonth()) {
                var days = today.getDate() - date.getDate();
                if (days == 0) {
                    human_date = 'today';
                } else if (days == -1) {
                    human_date = 'tomorrow';
                } else if (days == 1) {
                    human_date = 'yesterday';
                }
            }
        }
        if (!human_date) {
            human_date = Format.date(date, 'M j');
        }
        return human_date;
    },
        
    time: function (str, seconds) {
        var time = Date.parseTime(str);
        var hour = time.hour;
        var mins = time.mins;
        var secs = time.secs;
        
        var hour = hour.toString().pad(2, '0');
        var mins = mins.toString().pad(2, '0');
        if (secs != undefined) {
            secs = secs.toString().pad(2, '0');
        }
    
        return hour + ':' + mins + (seconds ? ':' + secs : '');
    },
    
    humanSize: function (size) {
        i = 0;
        iec = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        while (size / 1024 >= 1) {
            size /= 1024;
            i ++;
        }
        return Math.round(size, 1) + ' ' + iec[i];
    }
});