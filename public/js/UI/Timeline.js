include('mohawk.utils.Date');
include('utils.Format');

window.Timeline = new Class({
    cell_width: 111, // width + padding + border
    flag_width: 10,
    data: {},
    dragStart: 0,
    dragOffset: 0,
    earliest: null,
    latest: null,

    build: function () {
        var frame = document.createElement('DIV');
        self.frame = frame;
        frame.Timeline = self;
        
        var table = document.createElement('TABLE');

        self.table = table;
        table.Timeline = self;
        
        table.onmousedown = self.pick;
        
        frame.onmousewheel = function (event) {
            event = Mohawk.DOM.event(event);
            
            var frame = this;
            var self = frame.Timeline;
            
            var delta = event.wheel();
            
            var x = self.table.offsetLeft + delta * self.cell_width;
            if (x > 0) {
                x = 0;
            }
            if (x < -self.table.offsetWidth + frame.offsetWidth) {
                x = -self.table.offsetWidth + frame.offsetWidth;
            }
            self.table.style.left = x + 'px';
            
            try {
                event.preventDefault();
            } catch (e) {
                Console.describe(e);
            }
            return false;
        };
        if (FF) {
            frame.addEvent('DOMMouseScroll', frame.onmousewheel);
        }
        
        var thead = document.createElement('THEAD');
        table.appendChild(thead);
        var head_row = document.createElement('TR');
        thead.appendChild(head_row);
        
        var tbody = document.createElement('TBODY');
        table.appendChild(tbody);
        var body_row = document.createElement('TR');
        tbody.appendChild(body_row);
        
        var oneday = 3600 * 24 * 1000;
        
        var row   = null;
        var today = new Date();//Date.fromString(Data.today); // global var

        var max_days = 30;
        var per_row = 30;

        self.earliest = (new Date()).addDay(-7);
        self.latest   = self.earliest.addDay(max_days - 1);

        var i = 0;
        var day = self.earliest; // start date

        var prev = null;
        var _sub  = function () {
            if (i < max_days) {
                for (var j = 1; j <= per_row && i < max_days; j ++, i ++, day = new Date(day.valueOf() + oneday)) {
                	if (prev && prev.getDate() == day.getDate()) {
                		day = new Date(day.valueOf() + 3600 * 1000);
                	}
                	
                    var head_cell = self.createHeadCell(day, today);
                    head_row.appendChild(head_cell);
                    
                    var body_cell = self.createDataCell(day, today);
                    body_row.appendChild(body_cell);
                    
                    prev = new Date(day.valueOf());
                }
                setTimeout(_sub);
            } else {
                //self.setData();
                Progress.done(LNG.Done, true);
                self.moveTo(today, true);
            }
        };

        Progress.load(LNG.Loading);
        _sub();
        
        frame.appendChild(table);
    },
    
    pick: function (event) {
        event = Mohawk.DOM.event(event);
        
        var self = this.Timeline;
        var table = self.table;
        
        self.dragStart = event.cursor().x;
        self.dragOffset = table.offsetLeft;
        
        document.Timeline = self;

        self.frame.addEvent('mousemove', self.drag);
        self.frame.addEvent('mouseup', self.drop);
        
        return false;
    },
    
    drag: function (event) {
        event = Mohawk.DOM.event(event);
        
        var self = document.Timeline;
        var table = self.table;
        
        var cur = event.cursor().x;
        
        var x = self.dragOffset + (cur - self.dragStart);
        if (x > 0) {
            x = 0;
        }
        if (x < -table.offsetWidth + self.frame.offsetWidth) {
            x = -table.offsetWidth + self.frame.offsetWidth;
        }
        table.style.left = x + 'px';
    },
    
    drop: function (event) {
        event = Mohawk.DOM.event(event);
        
        var self = document.Timeline;

        self.frame.removeEvent('mousemove', self.drag);
        self.frame.removeEvent('mouseup', self.drop);
    },
    
    moveTo: function (date, no_animation) {
        var cell = self.getCell(date);
        var x = -cell.offsetLeft + (self.frame.offsetWidth - cell.offsetWidth) / 2;
        if (x > 0) {
            x = 0;
        }
        if (x < -self.table.offsetWidth + self.frame.offsetWidth) {
            x = -self.table.offsetWidth + self.frame.offsetWidth;
        }
        if (typeof(no_animation) != 'undefined' && no_animation) {
            self.table.style.left = x + 'px';
        } else {
            Mohawk.Effects.move(self.table, x, null, FF ? self.cell_width : 1, null, 1.1);
        }
    },
    
    startMoveBack: function () {
        Mohawk.Effects.move(self.table, 0, null, FF ? Math.round(self.cell_width / 3) : 2, null, 1.005);
    },
    
    startMoveForward: function () {
        Mohawk.Effects.move(self.table, -self.table.offsetWidth + self.frame.offsetWidth, null, FF ? Math.round(self.cell_width / 3) : 2, null, 1.005);
    },
    
    stopMove: function () {
        Mohawk.Effects.stop();
    },
    
    createHeadCell: function (day, today) {
        var cell = document.createElement('TH');
        
        var date = Format.date(day, ENV.language == 'ru' ? 'j F' : 'F j');
        var week = Format.date(day, 'l');
        
        var is_today = day.toString() == today.toString();
        cell.setHTML('<b>' + week + (is_today ? ' &darr;' : '') + '</b>' + date);
        
        if (is_today) {
            cell.addClass('today');
        }
        var w = day.getDay();
        if (w == 0 || w == 6) {
            cell.addClass('weekend');
        }
        
        return cell;
    },
    
    createDataCell: function (day, today) {
        var cell = document.createElement('TD');
        cell.id = self.getId(day);
        cell.data = {date: day};
        cell.ondragover = function () {
            this.addClass('over');
        };
        cell.ondragout = function () {
            this.removeClass('over');
        };
        cell.ondrop = function () {
            this.removeClass('over');
        };
        
        if (day.toString() == today.toString()) {
            cell.addClass('today');
        }
        if (Array.find([0, 6], day.getDay()) !== false) {
            cell.addClass('weekend');
        }

        var list = document.createElement('UL');
        cell.appendChild(list);
        cell.list = list;
        
        return cell;
    },

    setData: function () {
        var data = Data.event;
        Data.event = [];
        foreach(data, function () {
            var item = this;
            Data.event[item.id] = item;
            self.placeMarker(item);
        });
    },
    
    getId: function (date) {
        return 'day-' + (date instanceof Date ? self.getDateId(date) : date);
    },
    
    getDateId: function (date) {
        var year  = date.getFullYear();
        var month = date.getMonth() + 1;
        var day   = date.getDate();
        return '' + year + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day;
    },
        
    getCell: function (date) {
        return ID(self.getId(date));
    }
});