include('mohawk.kernel.Ajax');

window.CommunitiesApi = new Class({
    add: function (data) {
        var req = new Ajax(URL.home + 'api/communities/', Ajax.METHOD_POST);
        req.responseHandler = function (req) {
            if (req.data.item) {
                Observer.fire('community-added', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                self.showErrors(Communities.Form, req.data.error);
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
        var req = new Ajax(URL.home + 'api/communities/' + id + '/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
            if (req.data.item) {
                Observer.fire('community-edited', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                self.showErrors(Communities.Form, req.data.error);
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
        var req = new Ajax(URL.home + 'api/communities/' + id + '/', Ajax.METHOD_DELETE);
        req.responseHandler = function (req) {
            if (req.data.item) {
                var item = req.data.item;
                Observer.fire('community-removed', item);
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
    }
});