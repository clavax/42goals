include('mohawk.utils.Date');
include('utils.Format');

window.ProfileGoals = new Class({
    __construct: function (id, goals) {
        self.table = DOM.element('table', {
            id: id
        });
        
        self.head = DOM.element('thead', {
            appendTo: self.table
        });
        
        self.headRow = DOM.element('tr', {
            appendTo: self.head
        });
        
        self.tbody = DOM.element('tbody', {
            appendTo: self.table
        });
        
//        var today = (new Date()).clearTime();
//        var day = today.addDay(1 - (today.getDay() || 7));
        var today = Date.fromString(Data.today);
        var day = Date.fromString(Data.start || Data.today);
        self.day = day.clone();

        var th = DOM.element('th', {
            appendTo: self.headRow
        });
        if (ENV.UID == ENV.current_user.id) {
            DOM.element('img', {
                src: URL.img + 'site/chart-edit.png',
                appendTo: th,
                onclick: function () {
                    Observer.fire('profile-goals-edit-click');
                }
            });
        }
        
        for (var i = 1; i <= 7; i ++, day = day.addDay()) {
            var cell = self.createHeadCell(day, today);
            self.headRow.appendChild(cell);
        }
        
        foreach(goals, function (i, goal) {
            var row = self.createRow(goal);
            self.tbody.appendChild(row);
        });
    },
    
    createHeadCell: function (day, today) {
        var cell = document.createElement('TD');
        cell.data = {date: day};
        cell.id = day.getId();
        
        var date = Format.date(day, ENV.language == 'ru' ? 'j M' : 'M j');
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

    createRow: function(data) {
        var row = DOM.element('tr', {
            id: 'goal-' + data.id
        });
        if (data.privacy != 'public' && data.user == ENV.UID) {
            row.addClass('hidden');
        }
        row.data = data;
        
        var title = DOM.element('th', {
            html: data.title,
            appendTo: row
        });

        if (data.user != ENV.current_user.id) {
            title.setHTML('');
            
            DOM.element('img', {
//                src: data.user_data.thumbnail ? URL.userpics + data.user_data.thumbnail : URL.img + 'site/no-thumbnail.png',
                src: URL.img + 'site/compared-with.png',
                appendTo: title
            });
            
            if (data.user_data) {
                DOM.element('a', {
                    href: URL.home + 'users/' + data.user_data.login + '/goals/',
                    html: data.user_data.name || '?',
                    appendTo: title
                });
            } else {
                DOM.element('i', {
                    html: '?',
                    appendTo: title
                });
            }
        }
        
        var head = self.headRow.childNodes;
        for (var i = 1; i < head.length; i ++) {
            var col = head[i];
            var cell = self.createDataCell(row, col);
            row.appendChild(cell);
        }
        
        return row;
    },
    
    createDataCell: function (row, col) {
        var id = row.data.id;
        var date = Format.date(col.data.date, 'Y-m-d');
        var data = self.getData(id, date);
        var value = data.value || 0;
        
        var cell = DOM.element('td', {
            id: id + '-' + date
        });

        if (col.hasClass('today')) {
            cell.addClass('today');
        }
        
        cell.row = row;
        cell.col = col;
        
        switch(row.data.type) {
        case 'numeric':
            var number = DOM.element('DIV', {
                className: 'number',
                appendTo: cell,
                html: data.value
            });
            break;

        case 'counter':
            for (var i = 0; i < data.value; i ++) {
                if (i > 20) {
                    break;
                }
                var img = DOM.element('img', {
                    className: 'item',
                    src: Data.icons[cell.row.data.icon_item],
                    appendTo: cell
                });
            }
            break;
            
        case 'boolean':
            if (data.value === '1' || data.value === '0') {
                var img = DOM.element('img', {
                    src: data.value === '1' ? Data.icons[row.data.icon_true] : Data.icons[row.data.icon_false],
                    appendTo: cell
                });
            }
            break;
            
        case 'time':
        case 'timer':
            var time = DOM.element('div', {
                className: 'time',
                appendTo: cell
            });
            var html = '<span class="hours">{%h}</span>'
                     + '<span class="tick">:</span>'
                     + '<span class="mins">{%m}</span>'
                     + '<span class="secs">{%s}</span>';
            time.setHTML(self.formatTime(data.value, html));
            break;
            
        default:
            // console.log(data);
        }
        cell.addClass(row.data.type);
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
    
    getData: function (id, date) {
        var data = {value: ''};
        if (id in Data.data) {
            if (date in Data.data[id]) {
                data = Data.data[id][date];
            }
        }
        return data;
    }
});