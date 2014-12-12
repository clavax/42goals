include('mohawk.UI.Table');
include('UI.RangeCalendar');

window.PlanTable = Mohawk.UI.Table.extend({
    __construct: function (id) {
        var data = [];
        var head = {
            date: LNG.Date,
            value: LNG.Value
        };
        parent.__construct(id, data, head);
        
        var foot = self.createFoot();
        self.element.appendChild(foot);

        self.Calendar = new RangeCalendar('plan-calendar');
        self.Calendar.element.addClass('shadowed');
                
        Observer.add(self.Calendar.EVENT_ENDDATE_SET, function (date) {
            if (!date) {
                return;
            }
            self.Calendar.hide();
            
            var startdate = self.Calendar.startdate;
            var enddate = self.Calendar.enddate;
            if (self.current) {
                var row = self.getRow(self.current);
                var data = {goal: self.goal.id, startdate: startdate.getId(), enddate: enddate.getId()};
                Goals.editPlan(row.data.id, data);
                self.editRow(row, data);
            } else {
                var data = {
                    id: 'new-' + (new Date).valueOf(),
                    goal: self.goal.id,
                    startdate: startdate.getId(), 
                    enddate: enddate.getId(),
                    value: ''
                };
                var row = self.addRow(data);
                Observer.add('plan-added-' + data.id, function (new_id) {
                    self.editRow(row, {id: new_id});
                });
                Goals.addPlan(data);
            }
        });
    },
    
    createFoot: function () {
        var foot = DOM.element('TFOOT');
        var row  = DOM.element('TR');
        foot.appendChild(row);
        
        var cell = self.createCellDate(0, 'date', null);
        cell.colSpan = 2;
        row.appendChild(cell);
        
        return foot;
    },
    
    createRow: function (data) {
        data.date = {start: data.startdate, end: data.enddate};
        return parent.createRow(data);
    },
    
    createCellDate: function (row, col, data) {
        var cell = parent.createCell(row, col, data, true);
        
        var startdate, enddate;
        if (data) {
            startdate = Date.fromString(data.start);
            enddate = Date.fromString(data.end);
        }
        
        var p = DOM.element('P');
        
        if (data) {
            var remove = DOM.element('IMG');
            remove.src = URL.img + 'site/trash.png';
            remove.appendTo(p);
            remove.onclick = function () {
                var row = self.getRow(cell.row);
                Goals.removePlan(row.data.id, row.data.goal);
                Effects.vanish(row, function () {
                    self.removeRow(row);
                });
            };
        }
        
        var link = DOM.element('A');
        link.href = '#set-date';
        link.addClass('script');
        link.setHTML(startdate ? Format.dateRange(startdate, enddate) : LNG.Set_date);
        link.onclick = function (event) {
            event = DOM.event(event);
            event.stopPropagation();
            
            document.body.appendChild(self.Calendar.element);
            if (data) { 
                self.Calendar.startdate = startdate;
                self.Calendar.enddate = enddate;
            } else {
                self.Calendar.startdate = null;
                self.Calendar.enddate = null;
            }
            self.Calendar.setDate(startdate ? startdate : new Date);
            Dragdrop.bringToFront(self.Calendar.element);
            
            self.Calendar.element.adjoinTo(link, 'bottom', true);
            self.Calendar.element.alignTo(link, 'left', true);
            
            self.current = row;
            return false;
        };
        link.appendTo(p);
        
        cell.setChild(p);
        
        return cell;
    },
    
    createCellValue: function (row, col, data) {
        var cell = parent.createCell(row, col, data);
        
        cell.setHTML('');

        var input = DOM.element('input');

        if (Goals.Edit.data.type == 'time' || Goals.Edit.data.type == 'timer') {
            input.value = Goals.Table.formatTime(data, '{%h}:{%m}');
        } else {
            input.value = data;
        }
        cell.appendChild(input);
        
        input.title = LNG.Input_value;
        input.onfocus = function () {
            if (input.value == input.title) {
                input.value = '';
            }
            input.removeClass('empty');
        };
        input.onblur = function () {
            if (input.value == '') {
                input.value = input.title;
                input.addClass('empty');
            }
        };
        input.onchange = function () {
            var value = input.value;
            if (Goals.Edit.data.type == 'time' || Goals.Edit.data.type == 'timer') {
                value = Goals.Time.toSeconds(input.value);
            }
            Goals.editPlan(self.getRow(cell.row).data.id, {goal: self.goal.id, value: value});
        };
        input.blur();
        
        return cell;
    }
});