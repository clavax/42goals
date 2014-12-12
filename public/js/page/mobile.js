include('UI.MobileNumber');
include('UI.Shadow');

document.addLoader(function () {
    if (document.body.id != 'mobile-page') {
        return;
    }
        
    window.Mobile = new Singletone({
        __construct: function () {
            self.Number = new MobileNumber;
            self.Number.element.id = 'mobile-number';
            self.Number.element.appendTo(document.body);
            Shadow.element.appendTo(document.body);
            self.date = Data.today;
        },
        
        input: function (id) {
            var goal = Data.goals[id];
            if (!goal) {
                return;
            }
                        
            switch (goal.type) {
            case 'numeric':
                Mobile.Number.show(id);
                break;
                
            case 'boolean':
                self.iterate(id);
                break;
                
            case 'counter':
                self.increment(id);
                break;
                
            case 'time':
            case 'timer':
                try {
                    var h = document.getElementsByName('data[' + id + '][h]')[0].value;
                    var m = document.getElementsByName('data[' + id + '][m]')[0].value;
                    var s = document.getElementsByName('data[' + id + '][s]')[0].value;
                    goal.data.value = h * 3600 + m * 60 + s * 1;
                    self.save(id);
                } catch (e) {
                    console.log(e);
                }
                break;
            }
        },
        
        save: function (id) {
            var req = new Ajax(URL.home + 'api/goals/' + id + '/' + self.date + '/data/', Ajax.METHOD_POST);
            req.responseHandler = function (req) {
                if (req.data.ok) {
                    Progress.done('Saved', true);
                } else {
                    Progress.done('Error');
                }
            };
            req.errorHandler = function (req) {
                Progress.done('Handler: Error');
            };
            
            var data = {value: Data.goals[id].data.value};
            req.send(data);
            Progress.load('Saving');
        },
        
        iterate: function (id) {
            var goal = Data.goals[id];

            if (!goal) {
                return;
            }
            
            var row = ID('goal-' + id);
            if (!row) {
                return;
            }
            
            var cell = row.getElementsByTagName('TD')[0];
            var button = row.getElementsByClassName('add')[0].firstTag('BUTTON');
            var img = cell.firstTag('IMG');
            
            
            if (goal.data.value === 1) {
                img.src = goal.icon_false;
                img.removeClass('hidden');
                button.addClass('hidden');
                goal.data.value = 0;
            } else if (goal.data.value === 0) {
                img.addClass('hidden');
                button.removeClass('hidden');
                goal.data.value = '';
            } else {
                img.src = goal.icon_true;
                img.removeClass('hidden');
                button.addClass('hidden');
                goal.data.value = 1;
            }
            self.save(id);
        },
        
        decrement: function (id) {
            var goal = Data.goals[id];
            
            if (!goal) {
                return;
            }
            
            var row = ID('goal-' + id);
            if (!row) {
                return;
            }
            
            var cell = row.getElementsByTagName('TD')[0];
            var button = row.getElementsByClassName('add')[0].firstTag('BUTTON');
            var img = cell.firstTag('IMG');
            if (!img) {
                return;
            }
            
            img.purge();
            
            goal.data.value -= 1;
            self.save(id);
        },
        
        increment: function (id, no_save) {
            var goal = Data.goals[id];
            
            if (!goal) {
                return;
            }
            
            var row = ID('goal-' + id);
            if (!row) {
                return;
            }
            
            var cell = row.getElementsByTagName('TD')[0];
            var button = row.getElementsByClassName('add')[0].firstTag('BUTTON');
            
            var img = DOM.element('IMG');
            img.src = goal.icon_item;
            img.appendTo(cell);
            img.onclick = function () {
                self.decrement(id);
            };
            
            goal.data.value += 1;
            self.save(id);
        },
        
        setValue: function (id, value) {
            var goal = Data.goals[id];

            if (!goal) {
                return;
            }
            
            var row = ID('goal-' + id);
            if (!row) {
                return;
            }
            
            var cell = row.getElementsByTagName('TD')[0];
            var button = row.getElementsByClassName('add')[0].firstTag('BUTTON');

            switch (goal.type) {
            case 'numeric':
                var label = cell.firstTag('BIG');
                if (!label) {
                    label = DOM.element('BIG');
                    label.onclick = function () {
                        Mobile.Number.show(id);
                    };
                    cell.setChild(label);
                }
                label.setHTML(value);
                if (value === '') {
                    button.removeClass('hidden');
                } else {
                    button.addClass('hidden');
                }
                break;
                
            case 'boolean':
                var img = cell.firstTag('IMG');
                if (!img) {
                    img = DOM.element('IMG');
                    cell.setChild(img);
                    cell.onclick = function () {
                        self.iterate(id);
                    };
                }
                if (value === 1) {
                    img.src = goal.icon_true;
                    img.removeClass('hidden');
                    button.addClass('hidden');
                } else if (goal.data.value === 0) {
                    img.src = goal.icon_false;
                    img.removeClass('hidden');
                    button.addClass('hidden');
                } else {
                    img.addClass('hidden');
                    button.removeClass('hidden');
                }
                break;
                
            case 'counter':
                cell.purgeChildren();
                for (var i = 0; i < value && i < 100; i ++) {
                    var img = DOM.element('IMG');
                    img.src = goal.icon_item;
                    img.appendTo(cell);
                    img.onclick = function () {
                        self.decrement(id);
                    };
                }
                break;
            }
        }
    });

    foreach(Data.goals, function (i, goal) {
        var value = goal.data.value;
        if (goal.type == 'counter') {
            value = parseInt(value);
            if (isNaN(value)) {
                value = 0;
            }
        } else if (goal.type == 'numeric') {
            value = parseFloat(value);
            if (isNaN(value)) {
                value = '';
            }
        } else if (goal.type == 'boolean') {
            value = value === '' ? '' : (value === '1' ? 1 : 0);
        }
        Data.goals[i].data.value = value;
        Mobile.setValue(i, value);
    });
    
    ID('goals-form').onclick = function () {
        return false;
    };
    
    ID('tab-select').onchange = function () {
        var tab = this.value;
        foreach (ID('goals-table').tBodies[0].getElementsByTagName('tr'), function (i, row) {
            if (!tab || row.hasClass('tab-' + tab)) {
                row.removeClass('hidden');
            } else {
                row.addClass('hidden');
            }
        });
    };
});
