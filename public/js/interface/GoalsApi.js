include('mohawk.kernel.Ajax');

window.GoalsApi = new Class({
    add: function (data) {
        var req = new Ajax(URL.home + 'api/goals/', Ajax.METHOD_POST);
        req.responseHandler = function (req) {
            if (req.data.item) {
                self.addIcons(req.data.icon);
                Observer.fire('goal-added', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                self.showErrors(Goals.Add, req.data.error);
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        
        req.send({data: data});
        Progress.load(LNG.Saving);
    },
    
    edit: function (id, data) {
        var req = new Ajax(URL.home + 'api/goals/' + id + '/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
            if (req.data.item) {
                self.addIcons(req.data.icon);
                Observer.fire('goal-edited', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                self.showErrors(Goals.Edit, req.data.error);
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send({data: data});
        Progress.load(LNG.Saving);
    },
    
    addIcons: function (icons) {
        if (icons) {
            if (icons.id) {
                icons = [icons];
            }
            foreach(icons, function (i, icon) {
                Data.icons[icon.id] = icon.src;
            });
        }
    },
    
    showErrors: function (form, error) {
        foreach(error, 
            function (name) {
                var input = form.element[name];
                if (!input) {
                    return true;
                }
                var error = input.nextTag('B');
                if (!error) {
                    error = document.createElement('B');
                    error.addClass('error');
                    input.parentNode.insertAfter(error, input);
                }
                error.innerHTML = this;
            }
        );
    },
    
    remove: function (id) {
        if (!confirm(LNG.Confirm_delete)) {
            return;
        }
        var req = new Ajax(URL.home + 'api/goals/' + id + '/', Ajax.METHOD_DELETE);
        req.responseHandler = function (req) {
            if (req.data.item) {
                var item = req.data.item;
                Observer.fire('goal-removed', item);
                Progress.done(LNG.Deleted, true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send();
        Progress.load(LNG.Deleting);
    },
    
    archive: function (id) {
    	self.edit(id, {archived: Data.today});
    },
    
    restore: function (id) {
    	self.edit(id, {archived: null});
    },
    
    sort: function () {
        // get order
        var rows = Goals.Table.table.tBodies[0].childNodes;
        var pos = 0;
        var data = [];
        for (var i = 0; i < rows.length; i ++) {
            pos ++;
            data.push({id: rows[i].data.id, position: pos});
        }
        
        // send request
        var req = new Ajax(URL.home + 'api/goals/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
        	var item = req.data.item;
            if (item) {
            	if (item.id) {
            		item = [item];
            	} 
            	Observer.fire('goals-sorted', item);
                Progress.done(LNG.Saved, true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send({data: data});
        Progress.load(LNG.Saving);
    },
    
    save: function (id, date, data) {
        var req = new Ajax(URL.home + 'api/goals/' + id + '/' + date + '/data/', Ajax.METHOD_POST);
        req.responseHandler = function (req) {
            if (req.data.ok) {
                self.setData(id, date, data);
                
                var row = ID('goal-' + id);
                Goals.Table.createChart(row.data, row.getElementsByClassName('chart')[0]);
                Goals.Table.aggregate(row.data, row.getElementsByClassName('total')[0]);
                Goals.Table.setPlan(row);
                
                var cell = Goals.Table.getDataCell(id, date);
                if (data.text != undefined) {
	                if (data.text) {
	                	cell.addClass('commented');
	                } else {
	                	cell.removeClass('commented');
	                }
                }
                
                Progress.done(LNG.Saved, true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send(data);
        Progress.load(LNG.Saving);
    },
    
    addPlan: function (data) {
        var req = new Ajax(URL.home + 'api/plan/', Ajax.METHOD_POST);
        req.responseHandler = function (req) {
            if (req.data.item) {
                self.setPlan(req.data.item);
                self.Table.setPlan(ID('goal-' + data.goal));
                Observer.fire('plan-added-' + data.id, req.data.item.id);
                var row = ID('goal-' + req.data.item.goal);
                Goals.Table.aggregate(row.data, row.getElementsByClassName('total')[0]);
                Progress.done(LNG.Saved, true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send(data);
        Progress.load(LNG.Saving);
    },
    
    editPlan: function (id, data) {
        var req = new Ajax(URL.home + 'api/plan/' + id + '/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
            if (req.data.item) {
                self.setPlan(req.data.item);
                var row = ID('goal-' + req.data.item.goal);
                Goals.Table.aggregate(row.data, row.getElementsByClassName('total')[0]);
                self.Table.setPlan(row);
                Progress.done(LNG.Saved, true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send(data);
        Progress.load(LNG.Saving);
    },
    
    removePlan: function (id, goal) {
        var req = new Ajax(URL.home + 'api/plan/' + id + '/', Ajax.METHOD_DELETE);
        req.responseHandler = function (req) {
            if (req.data.ok) {
                self.unsetPlan(goal, id);
                self.Table.setPlan(ID('goal-' + goal));
                Progress.done(LNG.Deleted, true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send();
        Progress.load(LNG.Saving);
    },
    
    initSearch: function (input) {
        clearTimeout(self.search_timeout);
        self.search_timeout = setTimeout(function () {
            Goals.search(input.value);
        }, 1000);
    },
    
    search: function (query) {
        if (query.length < 3) {
            return;
        }
        var req = new Ajax(URL.home + 'api/templates/search/');
        req.responseHandler = function (req) {
            if (req.data.item) {
                var items = req.data.item;
                if (items.id) {
                    items = [items];
                }
                Goals.Add.Templates.setChildren(items);
                Progress.done(LNG.Done, true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send({q: query});
        Progress.load(LNG.Searching);
    },
    
    getData: function (id, date) {
        var data = {};
        if (id in Data.data && date in Data.data[id]) {
            data = Data.data[id][date];
        }
        if (data.value && data.value.match && data.value.match(/\d+(\.\d+)?/)) {
            data.value = parseFloat(data.value);
        }
        return data;
        
    },

    setData: function (id, date, data) {
        if (Data.data[id] == undefined) {
            Data.data[id] = {};
        }
        if (Data.data[id][date] == undefined) {
            Data.data[id][date] = {};
        }
        
        // copy values
        foreach(Object.keys(data), function (i, key) {
        	Data.data[id][date][key] = data[key];
        });
    },
    
    setPlan: function (data) {
        var id = data.id;
        var goal = data.goal;
        if (Data.plan[goal] == undefined) {
            Data.plan[goal] = {};
        }
        if (Data.plan[goal][id] == undefined) {
            Data.plan[goal][id] = {};
        }
        foreach(data, function (i, value) {
            Data.plan[goal][id][i] = value;
        });
    },
    
    unsetPlan: function (goal, id) {
        if (Data.plan[goal] == undefined) {
            return;
        }
        if (Data.plan[goal][id] == undefined) {
            return;
        }
        delete(Data.plan[goal][id]);
    }
});