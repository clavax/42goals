include('mohawk.kernel.Forms');
include('mohawk.utils.Date');
include('utils.Format');
include('utils.site');
include('UI.WeekPick');
Loader.includeTemplate('goals-stats');
Loader.includeTemplate('no-goals');
Loader.includeLanguage('goals');

window.GoalsWeek = new Class({
    day: null,
    
    build: function () {
        var frame = document.createElement('DIV');
        self.frame = frame;
        frame.ondragover = self.dragTitle;
        frame.ondrop = self.dropTitle;
        frame.ondragout = self.dragReset;
        frame.addClass('no-goals');
        
        var table = document.createElement('TABLE');
        self.table = table;
        frame.appendChild(table);
        
        var empty = DOM.element('div');
        empty.addClass('empty');
        empty.setHTML(Template.transform(NO_GOALS));
        frame.appendChild(empty);

        var head = document.createElement('THEAD');
        table.appendChild(head);
        var row = document.createElement('TR');
        head.appendChild(row);
        
        var tbody = document.createElement('TBODY');
        table.appendChild(tbody);
        
        var today = (new Date()).clearTime();
        var day = today.addDay(1 - (today.getDay() || 7));
        self.day = day.clone();
        
        var prev = false;

        for (var i = 1; i <= 7; i ++, day = day.addDay()) {
            var cell = self.createHeadCell(day, today);
            row.appendChild(cell);
        }
        
        // create link to previous week
        var first = DOM.element('TH');
        first.addClass('first');
        row.insertFirst(first);
        
        var prev = DOM.element('P');
        var prev_link = DOM.element('A');
        prev_link.addClass('script');
        prev_link.href = '#prev';
        prev_link.setHTML(Format.dateRange(self.day.addDay(-7), self.day.addDay(-1), '&mdash;'));
        prev_link.onclick = function () {
            self.moveTo(self.day.addDay(-7));
            return false;
        };
        prev.setHTML('&larr;&nbsp;');
        prev.appendChild(prev_link);
        first.appendChild(prev);
        self.prev_link = prev_link;
        
        // create link to next week
        var last = DOM.element('TH');
        last.addClass('last');
        row.appendChild(last);
        
        var next = DOM.element('P');
        var next_link = DOM.element('A');
        next_link.href = '#next';
        next_link.addClass('script');
        next_link.setHTML(Format.dateRange(self.day.addDay(7), self.day.addDay(13), '&mdash;'));
        next_link.onclick = function () {
            self.moveTo(self.day.addDay(7));
            return false;
        };
        next.setHTML('&nbsp;&rarr;');
        next.insertFirst(next_link);
        last.appendChild(next);
        self.next_link = next_link;
        
        // create calendar
        var cal = DOM.element('IMG');
        cal.addClass('open-calendar');
        cal.src = URL.img + 'site/calendar.png';
        cal.onclick = function (event) {
            event = DOM.event(event);
            var calendar = self.calendar;
            if (!calendar) {
                calendar = new WeekPick();
                calendar.today = new Date();
                self.calendar = calendar;
            }
            if (calendar.table.parentNode && calendar.table.parentNode.parentNode) {
                calendar.hide();
            } else {
                last.appendChild(calendar.table);
                calendar.setDate(self.day.getFullYear(), self.day.getMonth() + 1);
            }
            DOM.stopEvent(event);
        };
        last.appendChild(cal);
    },

    moveTo: function (day) {
        var today = new Date();
        var head = self.table.tHead.firstTag('TR').childNodes;
        var prev = false;
        self.day = day;
        for (var i = 1; i < head.length - 1; i ++, day = day.addDay()) {
            if (prev && prev.getDate() == day.getDate()) {
                // daylight saving
                day = new Date(day.valueOf() + 3600 * 1000);
            }
            var cell = self.createHeadCell(day, today);
            head[i].replace(cell);
            
            prev = new Date(day.valueOf());
        }
        self.prev_link.setHTML(Format.dateRange(self.day.addDay(-7), self.day.addDay(-1), '&mdash;'));
        self.next_link.setHTML(Format.dateRange(self.day.addDay(7), self.day.addDay(13), '&mdash;'));
        
        self.setData();
    },
    
    setData: function () {
        var goals = self.table.tBodies[0].getElementsByTagName('tr');
        for (i = 0; i < goals.length; i ++) {
        	Goals.Table.editGoal(goals[i].data);
        }
    },
        
    createRow: function(data) {
        var row = DOM.element('TR');
        row.id = 'goal-' + data.id;
        row.data = data;
        if (data.user == ENV.UID) {
            row.addClass('own');
        }
        
        var title = self.createTitleCell(data);
        row.appendChild(title);
        
        var head = self.table.tHead.firstTag('TR').childNodes;
        for (var i = 1; i < head.length - 1; i ++) {
            var col = head[i];
            var cell = self.createDataCell(row, col);
            row.appendChild(cell);
        }
        
        var last = self.createLastCell(data);
        row.appendChild(last);
        
        return row;
    },
    
    createChart: function (data, container) {
        container.setHTML('');
        
        var max, min = 0, neg = false, values = [];
        var day = self.day.addDay(-21);
        for (var i = 0; i < 28; i ++, day = day.addDay()) {
            var date = Format.date(day, 'Y-m-d');
            var value = Goals.getData(data.id, date).value;
            if (value === undefined || value === '') {
                continue;
            }
            value = parseFloat(value);
            if (data.type == 'boolean' && value === 0) {
                value = -1;
            }

            if (max == undefined || max < value) {
                max = value; 
            }
            if (min == undefined || min > value) {
                min = value; 
            }
            if (value < 0) {
                neg = true;
            }
            values.push(value);
        }
        
        var log = (max - min) > 1000;
        var scale = function (x) {
            return x > 0 ? Math.log(x) : (x < 0 ? -Math.log(-x) : 0);
        };
        if (log) {
            max = scale(max);
            min = scale(min);
        }
        
        var zero = max - min > 0 ? -min / (max - min) * 100 : '0';
        
        foreach(values, function (i, val) {
            if (log) {
                val = scale(val);
            }
            var col = DOM.element('SPAN');
            var height, bottom;
            if (max - min == 0 || val == 0) {
                height = 1;
                bottom = 0;
            } else {
                if (val > 0) {
                    height = val / (max - min) * 100;
                    bottom = -min / (max - min) * 100;
                } else {
                    height = Math.abs(val) / (max - min) * 100;
                    bottom = (val - min) / (max - min) * 100;
                    col.addClass('neg');
                }
            }
            col.style.height = height + '%';
            col.style.bottom = bottom + '%';
            col.style.left   = i * 2 + 'px';
            container.appendChild(col);
        });
        
        var axis = DOM.element('DIV');
        axis.addClass('axis');
        axis.style.bottom = zero + '%';
        container.appendChild(axis);
    },
    
    aggregate: function (data, container) {
        function _aggregate (start, length, no_unit) {
            var id = data.id;
            var max, min, sum = 0, count = 0, last = 0;
            var end = start.addDay(length - 1);
            var result = 0;
            if (data.type == 'boolean') {
                for (var day = start; day.le(end); day = day.addDay()) {
                    var date = Format.date(day, 'Y-m-d');
                    var value = Goals.getData(id, date).value;
                    if (value === 1) {
                        sum ++;
                        count ++;
                    } else if (value === 0) {
                        count ++;
                    }
                }
    
                result = count ? sum / count : 0;
            } else {
                for (var day = start; day.le(end); day = day.addDay()) {
                    var date = Format.date(day, 'Y-m-d');
                    var value = Goals.getData(id, date).value;
                    if (value == undefined || value === '') {
                        continue;
                    }
                    value = parseFloat(value);
                    if (isNaN(value)) {
                        continue;
                    }
                    if (max == undefined || max < value) {
                        max = value; 
                    }
                    if (min == undefined || min > value) {
                        min = value; 
                    }
                    sum += value;
                    count += 1;
                    last = value;
                }
                
                switch (data.aggregate) {
                case 'sum':
                    result = sum;
                    break;
                case 'avg':
                    result = sum / count;
                    break;
                case 'max':
                    result = max || 0;
                    break;
                case 'min':
                    result = min || 0;
                    break;
                default:
                    result = last;
                }
            }
            
            if (result != 0) {
                result = Math.round(result, 1);
                if (data.type == 'time' || data.type == 'timer') {
                	result = self.formatTime(result, '{%h}:{%m}');
                }
                result = result.toString();
                if (result.length && data.unit !== null && data.unit.length && !no_unit) {
                    if (data.prepend == 'yes') {
                        result = '<small>' + data.unit + '</small> <b>' + result + '</b>';
                    } else {
                        result = '<b>' + result + '</b> <small>' + data.unit + '</small>';
                    }
                } else {
                    result = '<b>' + result + '</b>';
                }
            } else {
                if (result === 0) {
                    result = '<b>' + result + '</b>';
                }
            }
            
            return result;
        }
        
        var this_month_start = self.day.addDay(- self.day.getDate() + 1);
        var today = (new Date()).clearTime();
        if (today.isBetween(self.day, self.day.addDay(6))) {
            this_month_start = today.addDay(- today.getDate() + 1);
        }
        var last_month_start = this_month_start.addDay(- this_month_start.getDate());
        last_month_start = last_month_start.addDay(- last_month_start.getDate() + 1);
        var last_week_start = self.day.addDay(-7);
        
        var this_month = _aggregate(this_month_start, this_month_start.getDaysInMonth(), true);
        var last_month = _aggregate(last_month_start, last_month_start.getDaysInMonth(), true);
        var this_week = _aggregate(self.day, 7, true);
        var last_week = _aggregate(last_week_start, 7, true);
        
        var week_start = self.day, week_end = self.day.addDay(6);
        var this_month_end = this_month_start.addDay(this_month_start.getDaysInMonth() - 1);
        
        var week_plan = {
            startdate: week_start.getId(),
            enddate: week_end.getId(),
            goal: data.id
        };
        var month_plan = {
            startdate: this_month_start.getId(),
            enddate: this_month_end.getId(),
            goal: data.id
        };
        var plans = Data.plan[data.id];
        if (plans != undefined) {
            foreach(plans, function (i, plan) {
                if (week_start.getId() == plan.startdate && week_end.getId() == plan.enddate) {
                    week_plan.id = plan.id;
                    if (data.type == 'time' || data.type == 'timer') {
                        week_plan.value = self.formatTime(plan.value, '{%h}:{%m}');
                    } else {
                        week_plan.value = plan.value;
                    }
                }
                if (this_month_start.getId() == plan.startdate && this_month_end.getId() == plan.enddate) {
                    month_plan.id = plan.id;
                    month_plan.value = plan.value;
                    if (data.type == 'time' || data.type == 'timer') {
                        month_plan.value = self.formatTime(plan.value, '{%h}:{%m}');
                    } else {
                        month_plan.value = plan.value;
                    }
                }
            });
        }
        
        if (this_week || last_week || this_month || last_month || week_plan || month_plan) {
            Template.assign('goal', data);
            Template.assign('this_week', this_week);
            Template.assign('last_week', last_week);
            Template.assign('this_month', this_month);
            Template.assign('last_month', last_month);
            Template.assign('week_plan', week_plan);
            Template.assign('month_plan', month_plan);
            container.setHTML(Template.transform(GOALS_STATS));
        } else {
            container.setHTML('');
        }
    },
    
    setPlan: function (row) {
        var goal = row.data;
        var plans = Data.plan[goal.id];
        if (plans == undefined) {
            return;
        }
        
        var cells = row.getElementsByTagName('TD');
        
        var today = (new Date).clearTime();
        
        for (var i = 0; i < cells.length; i ++) {
            var cell = cells[i];
            var day = cell.col.data.date;
            
            // find plan for the day
            var day_plan = false;
            foreach(plans, function (i, plan) {
                var start = Date.fromString(plan.startdate);
                var end = Date.fromString(plan.enddate);
                plan.period = end.daysFrom(start) + 1;
                if (day.isBetween(start, end) && (!day_plan || day_plan.period > plan.period)) {
                    day_plan = plan;
                    return true;
                }
            });
            
            // if no plan found
            if (!day_plan) {
                cell.plan = '';
                cell.showPlan();
                continue;
            }
            
            // apply plan value after it's found
            cell.plan = day_plan.value;
            if (goal.aggregate == 'sum' || goal.aggregate == 'avg') {
                if (day.lt(today)) {
                    cell.plan = '';
                } else {
                    var value = 0, sum = 0, start = Date.fromString(day_plan.startdate), end = Date.fromString(day_plan.enddate);
                    for (var d = start; d.le(today); d = d.addDay()) {
                        value = parseFloat(Goals.getData(goal.id, d.getId()).value);
                        if (isNaN(value)) {
                            value = 0;
                        }
                        sum += value;
                    }
                   // var days_left = end.daysFrom(today) + 1;
                    var days_left = end.daysFrom(today);
                    if (goal.aggregate == 'sum') {
                        cell.plan = (day_plan.value - sum) / days_left;
                    } else {
                        cell.plan = (day_plan.value * day_plan.period - sum) / days_left;
                    }
                    /*if (day.eq(today)) {
                        cell.plan += parseFloat(Goals.getData(goal.id, today.getId()).value) || 0;
                    }*/
                }
                
                // cell.plan = day_plan.value / day_plan.period;
                
                if (goal.type == 'counter') {
                    cell.plan = Math.ceil(cell.plan);
                    if (cell.plan < 0) {
                        cell.plan = 0;
                    }
                } else {
                    cell.plan = Math.round(cell.plan, 1);
                }
            } else {
                cell.plan = day_plan.value;
            }
            cell.showPlan();
        }
    },
    
    createTitleCell: function (data) {
        var cell = DOM.element('TH');
        cell.addClass('title');
        
        var wrap = DOM.element('DIV');
        wrap.addClass('wrap');
        cell.appendChild(wrap);
        
        var title = DOM.element('H2');
        if (!data.user || data.user == ENV.UID) {
            var str_title = data.title || '???';
            var size = 18;
            var max_len = 12;
            if (str_title.length > max_len) {
                size = Math.floor(max_len / str_title.length * size);
                if (size < 12) {
                    size = 12;
                }
            }
            title.style.fontSize = size + 'px';
            title.setHTML(htmlspecialchars(str_title));
            title.onclick = function () {
                Goals.Edit.setData(cell.parentNode.data);
            };
        } else {
            DOM.element('img', {
                src: URL.img + 'site/compared-with.png',
                appendTo: title
            });
            DOM.element('a', {
                href: URL.home + 'users/' + data.user_data.login + '/goals/',
                html: data.user_data.name,
                appendTo: title
            });
        }
        wrap.appendChild(title);

        var drag = DOM.element('IMG');
        drag.src = URL.img + 'site/move.gif';
        drag.addClass('drag');
        drag.ondragstart = function () {return false;}; // prevent image selection in IE
        drag.Timeline = self;
        wrap.appendChild(drag);
        drag.onmousedown = function (event) {
            event = DOM.event(event);
            if (event.button != BTN_LEFT) {
                return;
            }
            self.clone = self.createClone(cell);
            var tabs = [self.frame];
            for (var i = 0; i < Goals.Tabs.element.childNodes.length; i ++) {
            	tabs.push(Goals.Tabs.element.childNodes[i]);
            };
            Dragdrop.setTarget.apply(Dragdrop, tabs);
            Dragdrop.pick(event, [self.clone]);
            return false;
        };
        
        return cell;
    },
    
    createLastCell: function (data) {
        var cell = DOM.element('TH');
        cell.addClass('last');
        
        var total = DOM.element('DIV');
        total.addClass('total');
        cell.appendChild(total);
        self.aggregate(data, total);
        
        var chart = DOM.element('DIV');
        chart.addClass('chart');
        chart.onclick = function () {
            Goals.Edit.showChart(data);
        };
        cell.appendChild(chart);
        
        return cell;
    },

    createClone: function (cell) {
        var clone = DOM.element('DIV');
        self.frame.appendChild(clone);
        clone.setHTML(cell.getElementsByTagName('H2')[0].innerHTML);
        clone.style.position = 'absolute';
        clone.style.left = cell.offsetLeft + 'px';
        clone.style.top = cell.offsetTop + (cell.offsetHeight - clone.offsetHeight) / 2 + 'px';
        clone.style.zIndex = 10;
        clone.addClass('clone', 'rounded', 'draggable');
        clone.proto = cell;
        clone.ondrop = function () {
        	clone.purge();
        };
        return clone;
    },
    
    dragTitle: function (event) {
        event = DOM.event(event);
        event.preventDefault();
        
        var self = Goals.Table;
        var table = self.table;
        
        var y = event.cursor().y - self.frame.coordinates().y;
        var x = event.cursor().x - self.frame.offsetLeft;
        
        if (x > self.table.tHead.firstTag('TR').firstChild.offsetWidth) {
        	self.dragReset();
        	return;
        }

    	var titles = self.table.tBodies[0].getElementsByClassName('title');
        var node = null;
        var first = false;
        if (y < titles[0].offsetTop + titles[0].offsetHeight / 2) {
            node = titles[0];
            first = true;
        }
        for (var i = 0; i < titles.length; i ++) {
            if (y > titles[i].offsetTop + titles[i].offsetHeight / 2) {
                node = titles[i];
            }
            titles[i].removeClass('after', 'before');
        }
        if (node) {
            node.addClass(first ? 'before' : 'after');
        }
        self.node = node;
        self.first = first;
    },
    
    dropTitle: function (event) {
        event = DOM.event(event);
        
        var self = Goals.Table;
        
        var dragged_row = self.clone.proto.ancestorTag('TR');
        var marker_row = self.node.ancestorTag('TR');
        if (dragged_row != marker_row) {
            if (self.first) {
                dragged_row.parentNode.insertFirst(dragged_row);
            } else {
                dragged_row.parentNode.insertAfter(dragged_row, marker_row);
            }
        }
        Goals.sort();
        self.dragReset();
    },
    
    dragReset: function () {
    	var self = Goals.Table;
    	
    	if (self.node) {
    		self.node.removeClass('after', 'before');
    	}
        self.sorting = false;
        self.node = null;
        self.before = null;
    },
    
    createHeadCell: function (day, today) {
        var cell = document.createElement('TD');
        cell.data = {date: day};
        cell.id = self.getId(day);
        
        var date = Format.date(day, ENV.language == 'ru' ? 'j F' : 'F j');
        var week = Format.date(day, 'D');
        
        var is_today = day.getFullYear() == today.getFullYear() && day.getMonth() == today.getMonth() && day.getDate() == today.getDate();
        cell.setHTML('<b>' + week + '</b><br />' + date);
        
        if (is_today) {
            cell.addClass('today');
        }
        var w = day.getDay();
        if (w == 0 || w == 6) {
            cell.addClass('weekend');
        }
        
        return cell;
    },
    
    createDataCell: function (row, col) {
        var id = row.data.id;
        var date = Format.date(col.data.date, 'Y-m-d');
        var value = Goals.getData(id, date).value;
        
        var cell = DOM.element('TD');
        cell.id = self.getDataCellId(id, date);
        cell.row = row;
        cell.col = col;
        cell.onmousedown = function () {
            // prevent text selection
            return false;
        };
        cell.onselect = DOM.stopEvent;
        if (col.hasClass('today')) {
            cell.addClass('today');
        }
        
        var wrap = DOM.element('DIV');
        cell.appendChild(wrap);
        wrap.addClass('wrap');
        cell.wrap = wrap;
        
        var comment = DOM.element('DIV');
        comment.addClass('comment');
        wrap.appendChild(comment);
        comment.onclick = function (event) {
            event = DOM.event(event);
            event.stopPropagation();
            Goals.Comment.set(cell);
        };
        
        var data = Goals.getData(id, date);
        if (data.text) {
        	cell.addClass('commented');
        }
        /*
        cell.onmouseover = function () {
            cell.title = Goals.getData(id, date).text || '';
        };
        */
        
        cell.showPlan = function () {
            
        };
        
        switch(row.data.type) {
        case 'numeric':
            var number = DOM.element('DIV');
            number.addClass('number');
            wrap.appendChild(number);
            
            cell.number = number;
            
            cell.onmousedown = DOM.stopEvent;
            cell.onclick = function (event) {
                if (cell.row.data.user != ENV.UID) {
                    return;
                }
                event = DOM.event(event);
                event.stopPropagation();
                Goals.Number.set(cell);
            };
            
            cell.addUnit = function (value) {
                var number = cell.number;
                
                // fix size
                var size = 18;
                if (value.length > 6) {
                    var size = Math.floor(7 / value.length * 18);
                    if (size < 8) {
                        size = 8;
                    }
                }
                number.style.fontSize = size + 'px';
                
                if (value < 0) {
                    number.addClass('neg');
                } else {
                    number.removeClass('neg');
                }
                var unit = number.firstTag('SPAN');
                if (value.length && cell.row.data.unit && cell.row.data.unit.length) {
                    if (!unit) {
                        unit = DOM.element('SPAN');
                        unit.addClass('unit');
                        if (cell.row.data.prepend == 'yes') {
                            number.addClass('left');
                            number.insertFirst(unit);
                        } else {
                            number.appendChild(unit);
                        }
                    }
                    unit.setHTML(htmlspecialchars(cell.row.data.unit));
                } else {
                    if (unit) {
                        unit.purge();
                    }
                }
            };
            
            cell.setValue = function (value) {
                var value = value != undefined ? value.toString() : '';
                cell.value = value;
                cell.number.setHTML(value);
                
                if (value === '') {
                    cell.showPlan();
                    return;
                } else {
                    cell.number.removeClass('plan');
                }
                
                cell.addUnit(value);
            };
            
            cell.showPlan = function () {
                if (cell.value != undefined && cell.value !== '') {
                    return;
                }
                
                var plan = cell.plan ? cell.plan.toString() : '';
                cell.number.setHTML(plan);
                cell.number.addClass('plan');
                cell.addUnit(plan);
            };
            
            cell.setValue(value);
            break;

        case 'counter':
            var zero_enabled = false;
            
            cell.zero = function () {
                while (cell.getElementsByClassName('item').length) {
                    cell.getElementsByClassName('item')[0].purge();
                }
                var img = DOM.element('IMG');
                img.addClass('zero');
                img.src = Data.icons[cell.row.data.icon_zero];
                img.onclick = function (event) {
                    event = DOM.event(event);
                    event.stopPropagation();
                    img.remove();
                    cell.value = '';
                    Goals.save(id, Format.date(cell.col.data.date, 'Y-m-d'), {value: cell.value});
                };
                wrap.insertFirst(img);
                cell.value = 0;
            };
            
            cell.increment = function () {
                if (cell.value === '' && zero_enabled) {
                    cell.zero();
                    return;
                }
                while (cell.getElementsByClassName('zero').length) {
                    cell.getElementsByClassName('zero')[0].purge();
                }
                
                if (cell.value <= 50) {
                    var img = DOM.element('IMG');
                    img.addClass('item');
                    img.src = Data.icons[cell.row.data.icon_item];
                    img.onclick = function (event) {
                        if (cell.row.data.user != ENV.UID) {
                            return;
                        }
                        event = DOM.event(event);
                        event.stopPropagation();
                        img.remove();
                        cell.value --;
                        if (!cell.value && zero_enabled) {
                            cell.zero();
                        }
                        cell.showPlan();
                        Goals.save(id, Format.date(cell.col.data.date, 'Y-m-d'), {value: cell.value});
                    };
                    wrap.appendChild(img);
                }
                cell.value ++;
                cell.showPlan();
            };

            cell.setValue = function (value) {
                while (cell.getElementsByClassName('zero').length) {
                    cell.getElementsByClassName('zero')[0].purge();
                }
                while (cell.getElementsByClassName('item').length) {
                    cell.getElementsByClassName('item')[0].purge();
                }
                if (value == undefined || value === '') {
                    cell.value = '';
                    return;
                }
                value = parseInt(value);
                if (zero_enabled) {
                    cell.zero();
                }
                cell.value = 0;
                for (var i = 0; i < value; i ++) {
                    cell.increment();
                }
                cell.showPlan();
            };
            
            cell.showPlan = function () {
                while (cell.getElementsByClassName('plan').length) {
                    cell.getElementsByClassName('plan')[0].purge();
                }
                for (var i = 0; i < cell.plan - cell.value; i ++) {
                    if (i >= 50) {
                        break;
                    }
                    var img = DOM.element('IMG');
                    img.src = Data.icons[cell.row.data.icon_item];
                    img.addClass('plan');
                    img.setOpacity(0.2);
                    img.appendTo(wrap);
                };
            };
            
            cell.onclick = function () {
                if (cell.row.data.user != ENV.UID) {
                    return;
                }
                cell.increment();
                Goals.save(id, Format.date(cell.col.data.date, 'Y-m-d'), {value: cell.value});
            };
            
            cell.setValue(value);
            break;
            
        case 'boolean':
            cell.setValue = function (val) {
                if (val === 1 || val === 0) {
                    if (!cell.img) {
                        cell.img = DOM.element('IMG');
                        wrap.appendChild(cell.img);
                    }
                    if (val) {
                        cell.img.src = Data.icons[row.data.icon_true];
                    } else {
                        cell.img.src = Data.icons[row.data.icon_false];
                    }
                    cell.value = val;
                } else {
                    if (cell.img) {
                        cell.img.purge();
                        cell.img = null;
                    }
                    cell.value = '';
                }
            };
            
            cell.iterate = function () {
                if (cell.value === 1) {
                    cell.setValue(0);
                } else if (cell.value === 0) {
                    cell.setValue('');
                } else {
                    cell.setValue(1);
                }
                Goals.save(id, Format.date(cell.col.data.date, 'Y-m-d'), {value: cell.value});
            };
            
            cell.onclick = function () {
                if (cell.row.data.user != ENV.UID) {
                    return;
                }
                cell.iterate();
            };
            
            cell.setValue(parseInt(value));
            break;
            
        case 'time':
            var time = DOM.element('DIV');
            time.addClass('time');
            wrap.appendChild(time);
            cell.time = time;
            
            cell.onmousedown = DOM.stopEvent;
            cell.onclick = function (event) {
                if (cell.row.data.user != ENV.UID) {
                    return;
                }
                event = DOM.event(event);
                event.stopPropagation();
                Goals.Time.set(cell);
            };
        	
            cell.setValue = function (val) {
                cell.value = val;
            	var html = '<span class="hours">{%h}</span>'
			   			 + '<span class="tick">:</span>'
			   			 + '<span class="mins">{%m}</span>'
			   			 + '<span class="secs">{%s}</span>';
                cell.time.setHTML(self.formatTime(val, html));
            };
            
            cell.showPlan = function () {
                if (cell.value != undefined && cell.value !== '') {
                    return;
                }
                
                if (cell.plan == undefined || cell.plan === '') {
                    return;
                }
                
                var plan = cell.plan ? cell.plan.toString() : '';
                var html = '<span class="hours">{%h}</span>'
                         + '<span class="tick">:</span>'
                         + '<span class="mins">{%m}</span>'
                         + '<span class="secs">{%s}</span>';

                cell.time.setHTML(self.formatTime(plan, html));
                cell.time.addClass('plan');
            };          
            
            cell.setValue(value);
            break;
            
        case 'timer':
            var start = DOM.element('IMG');
            start.src = URL.img + 'site/timer-start.png';
            start.addClass('timer', 'timer-start');
            wrap.appendChild(start);
            cell.start = start;
            
            var stop = DOM.element('IMG');
            stop.src = URL.img + 'site/timer-pause.png';
            stop.addClass('timer', 'timer-stop', 'hidden');
            wrap.appendChild(stop);
            cell.stop = stop;
            
            var time = DOM.element('DIV');
            time.addClass('time');
            wrap.appendChild(time);
            cell.time = time;
            
            cell.onmousedown = DOM.stopEvent;
            cell.onclick = function (event) {
                if (cell.row.data.user != ENV.UID) {
                    return;
                }
                event = DOM.event(event);
                event.stopPropagation();
                Goals.Time.set(cell);
            };
            
            cell.startTimer = function (start_time) {
            	if (!start_time) {
            		start_time = cell.timer_start || new Date();
            	}
            	cell.timer_start = start_time;
            	cell.timer_id = 0;
            	
            	cell.addClass('active');
            	cell.start.addClass('hidden');
            	cell.stop.removeClass('hidden');
            	
            	var cell_value = cell.value || 0;
            	
            	var timer_f = function () {
            		if (!cell.hasClass('active')) {
            			return;
            		}
            		var now = new Date();
            		cell.setValue(cell_value + (now.valueOf() - cell.timer_start.valueOf()) / 1000);
            		cell.timer_id = setTimeout(timer_f, 100);
            	};
            	
            	timer_f();
            };
            
            start.onmousedown = DOM.stopEvent;
            start.onclick = function (event) {
                if (cell.row.data.user != ENV.UID) {
                    return;
                }
            	event = DOM.event(event);
            	event.stopPropagation();
            	
            	cell.startTimer();
            	var start_time = Format.date(cell.timer_start, 'Y-m-d H:i:s');
            	Goals.save(id, Format.date(cell.col.data.date, 'Y-m-d'), {start: start_time});
            };
            
            cell.stopTimer = function () {
            	cell.removeClass('active');
            	cell.start.removeClass('hidden');
            	cell.stop.addClass('hidden');
            	
            	clearTimeout(cell.timer_id);
            	cell.timer_start = false;
            };
            
            stop.onmousedown = DOM.stopEvent;
            stop.onclick = function (event) {
                if (cell.row.data.user != ENV.UID) {
                    return;
                }
            	event = DOM.event(event);
            	event.stopPropagation();

            	cell.stopTimer();
            	Goals.save(id, Format.date(cell.col.data.date, 'Y-m-d'), {value: cell.value, start: null});
            };

            cell.setValue = function (val) {
                cell.value = val;
            	var html = '<span class="hours">{%h}</span>'
			   			 + '<span class="tick">:</span>'
			   			 + '<span class="mins">{%m}</span>'
			   			 + '<span class="secs">{%s}</span>';
                
                cell.time.setHTML(self.formatTime(val, html) || '&nbsp;');
                cell.time.removeClass('plan');
            };

            cell.showPlan = function () {
                if (cell.value != undefined && cell.value !== '') {
                    return;
                }
                
                if (cell.plan == undefined || cell.plan === '') {
                    return;
                }
                
                var plan = cell.plan ? cell.plan.toString() : '';
                var html = '<span class="hours">{%h}</span>'
                         + '<span class="tick">:</span>'
                         + '<span class="mins">{%m}</span>'
                         + '<span class="secs">{%s}</span>';

                cell.time.setHTML(self.formatTime(plan, html));
                cell.time.addClass('plan');
            };            
            
            cell.setValue(value);
            if (data.start) {
            	cell.startTimer(Date.fromString(data.start));
            }
            break;
            
        default:
            // console.log(data);
        }
        wrap.addClass(row.data.type);
        
        return cell;
    },
    
    formatTime: function (val, template) {
    	val = parseFloat(val);
    	if (isNaN(val)) {
    		return '';
    	}
    	
    	var hours = Math.floor(val / 3600);
    	val -= hours * 3600;
    	var mins  = Math.floor(val / 60);
    	var secs  = Math.floor(val - mins * 60);
    	
    	hours = hours.toString().pad(2, '0');
    	mins  = mins.toString().pad(2, '0');
    	secs  = secs.toString().pad(2, '0');
    	
    	Template.assign('h', hours);
    	Template.assign('m', mins);
    	Template.assign('s', secs);
    	
    	return Template.transform(template);
    },

    getId: function (date) {
        return 'day-' + (date instanceof Date ? Format.date(date, 'Y-m-d') : date);
    },
    
    getCell: function (date) {
        return ID(self.getId(date));
    },
    
    getRowId: function (id) {
    	return 'goal-' + id;
    },
    
    getRow: function (id) {
    	return ID(self.getRowId(id));
    },
    
    getDataCellId: function (id, date) {
    	return self.getRowId(id) + '-' + self.getId(date);
    },
    
    getDataCell: function (id, date) {
    	return ID(self.getDataCellId(id, date));
    },
    
    compareGoals: function (goal1, goal2) {
    	return parseInt(goal1.data.position) < parseInt(goal2.data.position);
    },
    
    placeGoal: function (row) {
        if (self.compareGoals instanceof Function) {
            var before = null;
            for (var i = 0; i < self.table.tBodies[0].childNodes.length; i ++) {
                var child = self.table.tBodies[0].childNodes[i];
                if (child == row) {
                    continue;
                }
                if (self.compareGoals(row, child)) {
                    before = child;
                    break;
                }
            }
            self.table.tBodies[0].insertBefore(row, before);
        } else {
        	self.table.tBodies[0].appendChild(row, child);
        }
    },
    
    addGoal: function (data) {
        var row = self.createRow(data);
        self.placeGoal(row);
        self.createChart(data, row.getElementsByClassName('chart')[0]);
        self.setPlan(row);
        self.frame.removeClass('no-goals');
    },
    
    editGoal: function (data) {
        var row = self.createRow(data);
        self.table.tBodies[0].replaceChild(row, ID('goal-' + data.id));
        self.createChart(data, row.getElementsByClassName('chart')[0]);
        self.setPlan(row);
    },
    
    removeGoal: function (id) {
        var row = ID('goal-' + id);
        Effects.vanish(row, function () {
            row.purge();    
            if (!self.table.tBodies[0].childNodes.length) {
            	self.frame.addClass('no-goals');
            }
        });
    },
    
    removeGoals: function () {
    	self.table.tBodies[0].purgeChildren();
    	self.frame.addClass('no-goals');
    }
});