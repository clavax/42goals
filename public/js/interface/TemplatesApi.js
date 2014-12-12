include('mohawk.kernel.Ajax');

window.TemplatesApi = new Class({
    add: function (data) {
        var req = new Ajax(URL.home + 'api/templates/', Ajax.METHOD_POST);
        req.responseHandler = function (req) {
            if (req.data.item) {
                self.addIcons(req.data.icon);
                Observer.fire('template-added', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                self.showErrors(Templates.Form, req.data.error);
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
        var req = new Ajax(URL.home + 'api/templates/' + id + '/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
            if (req.data.item) {
                self.addIcons(req.data.icon);
                Observer.fire('template-edited', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                self.showErrors(Templates.Form, req.data.error);
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
        var req = new Ajax(URL.home + 'api/templates/' + id + '/', Ajax.METHOD_DELETE);
        req.responseHandler = function (req) {
            if (req.data.item) {
                var item = req.data.item;
                Observer.fire('template-removed', item);
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
    
    sort: function () {
        // get order
        var items = Templates.List.element.childNodes;
        var pos = 0;
        var data = [];
        for (var i = 0; i < items.length; i ++) {
            pos ++;
            if (!items[i].data) {
            	continue;
            }
            data.push({id: items[i].data.id, position: pos});
        }
        
        // send request
        var req = new Ajax(URL.home + 'api/templates/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
        	var item = req.data.item;
            if (item) {
            	if (item.id) {
            		item = [item];
            	} 
            	Observer.fire('templates-sorted', item);
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
    }
});