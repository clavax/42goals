Date.DAY = 3600 * 24 * 1000;

Date.fromString = function (str) {
	var datetime = str.trim().split(' ');
	var date = datetime[0].split('-');
	if (datetime.length > 1) {
		var time = datetime[1].split(':');
	} else {
		var time = [0, 0, 0];
	}
    return new Date(date[0], date[1] - 1, date[2], time[0] || 0, time[1] || 0, time[2] || 0);
};

Date.prototype.getWeek = function (dowOffset) {
    /*getWeek() was developed by Nick Baicoianu at MeanFreePath: http://www.meanfreepath.com */
    dowOffset = typeof(dowOffset) == 'number' ? dowOffset : 0; //default dowOffset to zero
    var newYear = new Date(this.getFullYear(), 0, 1);
    var day = newYear.getDay() - dowOffset; //the day of week the year begins on
    day = (day >= 0 ? day : day + 7);
    var daynum = Math.floor((this.getTime() - newYear.getTime() - (this.getTimezoneOffset() - newYear.getTimezoneOffset()) * 60000) / 86400000) + 1;
    var weeknum;
    //if the year starts before the middle of a week
    if (day < 4) {
        weeknum = Math.floor((daynum + day - 1) / 7) + 1;
        if (weeknum > 52) {
            nYear = new Date(this.getFullYear() + 1, 0, 1);
            nday = nYear.getDay() - dowOffset;
            nday = nday >= 0 ? nday : nday + 7;
            /*if the next year starts before the middle of
              the week, it is week #1 of that year*/
            weeknum = nday < 4 ? 1 : 53;
        }
    } else {
        weeknum = Math.floor((daynum + day - 1) / 7);
    }
    return weeknum;
};

Date.prototype.getDaysInMonth = function () {
    var months = [31, Date.isLeapYear(this.getFullYear()) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    return months[this.getMonth()];
};

Date.isLeapYear = function (year) {
    return year % 4 ? false : (year % 100 ? true : (year % 400 ? false : true));    
};

Date.getWeekday = function (d, m, y) {
    var date = new Date(y, m - 1, d);
    var wd = date.getDay() || 7;
    return wd;
};

Date.prototype.clone = function () {
    return new Date(this.valueOf());
};

Date.prototype.addDay = function (num) {
    if (typeof(num) == 'undefined') {
        num = 1;
    }
    var date = this.clone();
    date.setDate(date.getDate() + num);
    
    return date;
};

Date.prototype.clearTime = function () {
    this.setHours(0);
    this.setMinutes(0);
    this.setSeconds(0);
    this.setMilliseconds(0);
    return this;
};

Date.prototype.daysFrom = function (date) {
    return Math.round((this.valueOf() - date.valueOf()) / Date.DAY);
};

Date.prototype.getMonthWeek = function () {
    var first_date = new Date(this);
    first_date.setDate(1);
    
    return this.getWeek(1) - first_date.getWeek(1) + 1;    
};

Date.prototype.getId = function (date) {
    var year  = this.getFullYear();
    var month = this.getMonth() + 1;
    var day   = this.getDate();
    return '' + year + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day;
};

Date.prototype.gt = function (date) {
    return this.valueOf() > date.valueOf();
};

Date.prototype.ge = function (date) {
    return this.valueOf() >= date.valueOf();
};

Date.prototype.eq = function (date) {
    return this.valueOf() == date.valueOf();
};

Date.prototype.ne = function (date) {
    return this.valueOf() != date.valueOf();
};

Date.prototype.le = function (date) {
    return this.valueOf() <= date.valueOf();
};

Date.prototype.lt = function (date) {
    return this.valueOf() < date.valueOf();
};

Date.prototype.isBetween = function (date1, date2) {
    return this.ge(date1) && this.le(date2);
};

Date.isTimeValid = function (str) {
    var time_regexp = new RegExp('^(\\d{1,2})[\.-:](\\d{1,2})(?:[\.-:](\\d{1,2}))?$');
    return !!time_regexp.exec(str);
};

Date.parseTime = function (str) {
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
};

Date.parse = function (str) {
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
};