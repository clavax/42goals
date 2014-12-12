include('mohawk.kernel.Ajax');

window.ChartsApi = new Class({
    add: function (data) {
        var req = new Ajax(URL.home + 'api/charts/', Ajax.METHOD_POST);
        req.responseHandler = function (req) {
            if (req.data.item) {
                Observer.fire('chart-added', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                self.showErrors(Charts.Form, req.data.error);
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
        var req = new Ajax(URL.home + 'api/charts/' + id + '/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
            if (req.data.item) {
                Observer.fire('chart-edited', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                self.showErrors(Charts.Form, req.data.error);
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send({data: data});
        Progress.load(LNG.Saving);
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
        var req = new Ajax(URL.home + 'api/charts/' + id + '/', Ajax.METHOD_DELETE);
        req.responseHandler = function (req) {
            if (req.data.item) {
                var item = req.data.item;
                Observer.fire('chart-removed', item);
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