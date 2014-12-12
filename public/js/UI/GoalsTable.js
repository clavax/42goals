include('UI.Timeline');
include('mohawk.kernel.Forms');
include('utils.site');

window.GoalsTable = Timeline.extend({
    cell_width: 111, // width + padding + border
    flag_width: 10,
    data: {},
    dragStart: 0,
    dragOffset: 0,
    earliest: null,
    latest: null,
    marginLeft: 150,

    build: function () {
        var frame = document.createElement('DIV');
        self.frame = frame;
        frame.Timeline = self;
        
        var table = document.createElement('TABLE');

        self.table = table;
        table.Timeline = self;
        
        frame.onmousewheel = function (event) {
            event = Mohawk.DOM.event(event);
            
            var frame = this;
            var self = frame.Timeline;
            
            var delta = event.wheel();
            
            var x = self.table.offsetLeft - self.marginLeft + delta * self.cell_width;
            if (x > 0) {
                x = 0;
            }
            if (x < -self.table.offsetWidth - self.marginLeft + frame.offsetWidth) {
                x = -self.table.offsetWidth - self.marginLeft + frame.offsetWidth;
            }
            self.table.style.left = x + 'px';
            
            self.fixTitles();
            self.setScroll();
            
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
        
        var th = DOM.element('TH');
        th.addClass('fixed');
        if (OPERA) {
            th.style.height = '10000px';
        }
        head_row.insertFirst(th);
        
        var tbody = document.createElement('TBODY');
        table.appendChild(tbody);
        
        var oneday = 3600 * 24 * 1000;
        
        var row   = null;
        var today = new Date();//Date.fromString(Data.today); // global var

        var max_days = 30;
        var per_row = 30;

        self.earliest = (new Date()).addDay(-max_days + 1);
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
        
        var scrollwrap = DOM.element('DIV');
        scrollwrap.addClass('scrollwrap');

        var scrollbar = DOM.element('DIV');
        scrollbar.addClass('scrollbar');
        self.scrollbar = scrollbar
        
        var scroll = DOM.element('DIV');
        scroll.addClass('scroll', 'draggable');
        scroll.dragY = false;
        scroll.onmousedown = Dragdrop.pick;
        scroll.Timeline = self;
        scroll.ondrag = function (event) {
            var self = this.Timeline;
            
            var pos = arguments[1];
            if (pos.x < 0) {
                pos.x = 0;
            }
            if (pos.x + this.offsetWidth > self.scrollbar.offsetWidth) {
                pos.x = self.scrollbar.offsetWidth - this.offsetWidth;
            }
            var percent = pos.x / (self.scrollbar.offsetWidth - this.offsetWidth);
            var scale = self.table.offsetWidth - self.scrollbar.offsetWidth;
            var left = scale * percent;
            self.table.style.left = -left + 'px';
            self.fixTitles();
        };
        self.scroll = scroll;
        
        scrollbar.appendChild(scroll);
        scrollwrap.appendChild(scrollbar);
        frame.appendChild(scrollwrap);
    },
    
    setScroll: function () {
        var percent = -(self.table.offsetLeft - self.marginLeft) / (self.table.offsetWidth - self.scrollbar.offsetWidth);
        var scale = self.scrollbar.offsetWidth - self.scroll.offsetWidth;
        self.scroll.style.left = scale * percent + 'px';
    },
    
    fixTitles: function () {
        if (FF) {
            return;
        }
        var left = - self.table.offsetLeft + self.marginLeft;
        foreach(self.table.tBodies[0].getElementsByTagName('TH'), function (i, th) {
            if (!th.style) {
                return;
            }
            th.style.left = left + 'px';
            th.style.marginLeft = -self.marginLeft + 'px';
        });
        var th = self.table.getElementsByClassName('fixed')[0];
        th.style.left = left + 'px';
        th.style.marginLeft = -self.marginLeft + 'px';
    },
    
    moveTo: function (date, no_animation) {
        var cell = self.getCell(date);
        var x = -cell.offsetLeft + (self.frame.offsetWidth - cell.offsetWidth) / 2;
        if (x > 0) {
            x = 0;
        }
        if (x < -self.table.offsetWidth - self.marginLeft + self.frame.offsetWidth) {
            x = -self.table.offsetWidth - self.marginLeft + self.frame.offsetWidth;
        }
        if (typeof(no_animation) != 'undefined' && no_animation) {
            self.table.style.left = x + 'px';
        } else {
            Mohawk.Effects.move(self.table, x, null, FF ? self.cell_width : 1, null, 1.1);
        }
        self.fixTitles();
        self.setScroll();
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
    
    createClone: function (cell) {
        var clone = DOM.element('DIV');
        self.frame.appendChild(clone);
        clone.setHTML(cell.firstTag('H2').innerHTML);
        clone.style.position = 'absolute';
        clone.style.left = cell.offsetLeft + self.OffsetLeft + 'px';
        clone.style.top = cell.offsetTop + (cell.offsetHeight - clone.offsetHeight) / 2 + 'px';
        clone.style.zIndex = 10;
        clone.addClass('clone', 'rounded');
        clone.proto = cell;
        return clone;
    },

    pickTitle: function (event) {
        event = DOM.event(event);
        event.preventDefault();
        
        var self = this.Timeline;
        var table = self.table;
        var cell = this.ancestorTag('TH');
        
        self.clone = self.createClone(cell);
        self.dragStart = event.cursor().y;
        self.dragOffset = self.clone.offsetTop;
        self.sorting = true;
        
        document.Timeline = self;

        self.frame.addEvent('mousemove', self.dragTitle);
        self.frame.addEvent('mouseup', self.dropTitle);
        
        return false;
    },
    
    dragTitle: function (event) {
        event = DOM.event(event);
        event.preventDefault();
        
        var self = document.Timeline;
        var table = self.table;
        
        var cur = event.cursor().y;
        
        var y = self.dragOffset + (cur - self.dragStart);
        if (y < 0) {
            y = 0;
        }
        if (y > -self.clone.offsetHeight + self.frame.offsetHeight) {
            y = -self.clone.offsetHeight + self.frame.offsetHeight;
        }
        
        var titles = self.table.tBodies[0].getElementsByTagName('TH');
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
        
        self.clone.style.top = y + 'px';
    },
    
    dropTitle: function (event) {
        event = DOM.event(event);
        
        var self = document.Timeline;
        
        var dragged_row = self.clone.proto.ancestorTag('TR');
        var marker_row = self.node.ancestorTag('TR');
        if (dragged_row != marker_row) {
            if (self.first) {
                dragged_row.parentNode.insertFirst(dragged_row);
            } else {
                dragged_row.parentNode.insertAfter(dragged_row, marker_row);
            }
        }
        self.node.removeClass('after', 'before');
        Goals.sort();

        self.clone.purge();
        self.sorting = false;
        self.node = null;
        self.before = null;

        self.frame.removeEvent('mousemove', self.dragTitle);
        self.frame.removeEvent('mouseup', self.dropTitle);
    },
    
    createRow: function(data) {
        var row = DOM.element('TR');
        row.id = 'goal-' + data.id;
        row.data = data;
        
        var th = DOM.element('TH');
        row.appendChild(th);
        
        var title = DOM.element('H2');
        var str_title = data.title;
        var size = 18;
        var max_len = 12;
        if (str_title.length > max_len) {
            size = Math.floor(max_len / str_title.length * size);
            if (size < 8) {
                size = 8;
            }
        }
        title.style.fontSize = size + 'px';
        
        title.setHTML(htmlspecialchars(str_title));
        title.onclick = function () {
            Goals.Form.set(row.data);
        };
        th.appendChild(title);
        
        var drag = DOM.element('IMG');
        drag.src = URL.img + 'site/move.gif';
        drag.addClass('drag');
        drag.onmousedown = self.pickTitle;
        drag.Timeline = self;
        th.appendChild(drag);
        
//        var graph = DOM.element('A');
//        graph.setHTML('<img src="' + URL.img + 'site/graph.png' + '" />');
//        graph.href = URL.home + 'report/' + data.id + '/';
//        graph.addClass('graph');
//        th.appendChild(graph);
        
        var graph = DOM.element('IMG');
        graph.src = URL.img + 'site/graph.png';
        graph.addClass('graph');
        graph.onclick = function () {
            Goals.Chart.draw(data.id);
        };
        th.appendChild(graph);
        
        var head = self.table.tHead.firstTag('TR').childNodes;
        for (var i = 1; i < head.length; i ++) {
            var value = '';
            var data = Object.clone(data);
            data.date = self.getDateId(head[i].data.date);
//            if (Data.data[data.id] != undefined && Data.data[data.id][data.date] != undefined) {
//                data.value = Data.data[data.id][data.date].value;
//                data.text  = Data.data[data.id][data.date].text;
//            }
            var cell = self.createDataCell(data);
            row.appendChild(cell);
        }
        
        return row;
    },
        
    createHeadCell: function (day, today) {
        var cell = document.createElement('TH');
        cell.data = {date: day};
        cell.id = self.getId(day);
        
        var date = Format.date(day, ENV.language == 'ru' ? 'j M' : 'M j');
        var week = Format.date(day, 'D');
        
        var is_today = day.toString() == today.toString();
        cell.setHTML('<b>' + week + (is_today ? ' &darr;' : '') + '</b><br />' + date);
        
        if (is_today) {
            cell.addClass('today');
        }
        var w = day.getDay();
        if (w == 0 || w == 6) {
            cell.addClass('weekend');
        }
        
        return cell;
    },
    
    createDataCell: function (data) {
        var value = Goals.getData(data.id, data.date).value;
        
        var cell = DOM.element('TD');
        cell.data = data;
        cell.onmousedown = DOM.stopEvent;
        
        var wrap = DOM.element('DIV');
        cell.appendChild(wrap);
        wrap.addClass('wrap');
        cell.wrap = wrap;
        wrap.fixHeight = function () {
            wrap.style.height = cell.offsetHeight -2 + 'px';
        };
        
        var comment = DOM.element('DIV');
        comment.addClass('comment');
        wrap.appendChild(comment);
        comment.onclick = function (event) {
            event = DOM.event(event);
            event.stopPropagation();
            Goals.Comment.set(cell);
        };
        
//        cell.onmouseover = function () {
//            clearTimeout(self.timeout);
//            self.timeout = setTimeout(function () {
//                Goals.Comment.set(cell);
//            }, 1000);
//        };
//
//        cell.onmouseout = function () {
//            clearTimeout(self.timeout);
//            if (Goals.Comment.cell == cell) {
//                self.timeout = setTimeout(function () {
//                    Goals.Comment.hide();
//                }, 1000);
//            }
//        };
        
        switch(data.type) {
        case 'numeric':
            var input = FormsInterface.createInput('text', 'data[' + data.id + '][' + data.date + ']', value || '');
            input.onmousedown = DOM.stopEvent;
            input.fixSize = function () {
                var size = 18;
                if (this.value.length > 7) {
                    var size = Math.floor(7 / this.value.length * 18);
                    if (size < 8) {
                        size = 8;
                    }
                }
                this.style.fontSize = size + 'px';
                
                if (this.value < 0) {
                    this.addClass('neg');
                } else {
                    this.removeClass('neg');
                }
                if (this.value.length && data.unit.length) {
                    var unit = this.nextTag('SMALL');
                    if (!unit) {
                        unit = DOM.element('SMALL');
                        this.parentNode.insertAfter(unit, this);
                    }
                    unit.setHTML(htmlspecialchars(data.unit));
                }
            };
            input.onchange = function () {
                this.fixSize();
                Goals.save(data.id, data.date, this.value);
            };
            input.fixSize();
            wrap.appendChild(input);
            break;

        case 'counter':
            cell.value = 0;
            cell.increment = function () {
                var img = DOM.element('IMG');
                img.src = Data.icons[data.icon_item];
                img.onclick = function (event) {
                    event = DOM.event(event);
                    event.stopPropagation();
                    img.remove();
                   // wrap.fixHeight();
                    cell.value --;
                    Goals.save(data.id, data.date, cell.value);
                };
                wrap.appendChild(img);
                //wrap.fixHeight();
                cell.value ++;
            };
            cell.onclick = function () {
                cell.increment();
                Goals.save(data.id, data.date, cell.value);
            };
            for (var i = 0; i < value; i ++) {
                cell.increment();
            }
            break;
            
        case 'boolean':
            cell.setTrue = function () {
                if (!cell.img) {
                    cell.img = DOM.element('IMG');
                    wrap.appendChild(cell.img);
                }
                cell.img.src = Data.icons[data.icon_true];
                cell.value = true;
            };
            cell.setFalse = function () {
                if (!cell.img) {
                    cell.img = DOM.element('IMG');
                    wrap.appendChild(cell.img);
                }
                cell.img.src = Data.icons[data.icon_false];
                cell.value = false;
            };
            cell.setNone = function () {
                if (cell.img) {
                    cell.img.purge();
                    cell.img = null;
                }
                cell.value = null;
            };
            cell.iterate = function () {
                if (cell.value === true) {
                    cell.setFalse();
                } else if (cell.value === false) {
                    cell.setNone();
                } else {
                    cell.setTrue();
                }
                Goals.save(data.id, data.date, cell.value);
            };
            cell.onclick = cell.iterate;
            if (value === 'true') {
                cell.setTrue();
            } else if (value === 'false') {
                cell.setFalse();
            } else {
                cell.setNone();
            }
            
            break;
            
        default:
            // console.log(data);
        }
        
        return cell;
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
    },
    
    addGoal: function (data) {
        var row = self.createRow(data);
        self.table.tBodies[0].appendChild(row);
        self.fixTitles();
    },
    
    editGoal: function (data) {
        var row = self.createRow(data);
        self.table.tBodies[0].replaceChild(row, ID('goal-' + data.id));
        self.fixTitles();
    },
    
    removeGoal: function (id) {
        var row = ID('goal-' + id);
        Effects.vanish(row, function () {
            row.purge();    
        });
    }
});