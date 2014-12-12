include('mohawk.kernel.Ajax');

window.FormProcessor = new Class ({
    api: false,
    method: false,
    form: false,

    submit: function (form) {
        self.form = form;

        var req = new Ajax(self.api || form.action, self.method || Ajax.METHOD_POST);
        
        req.responseHandler = function (req) {
            self.responseHandler.apply(self, [req]);
        };

        req.send(form.getData());

        self.doAfterSubmit(form);

        return false;
    },

    doAfterSubmit: function () {
        Progress.load(LNG.Submitting || 'Submitting');
    },
    
    responseHandler: function (req) {
        if (req.data.error) {
            self.errorHandler.apply(self, [req]);
        } else {
            self.successHandler.apply(self, [req]);
        }
    },

    successHandler: function (req) {
        Progress.done(LNG.Done || 'Done', true);
    },
    
    errorHandler: function (req) {       
        Progress.done(LNG.Error || 'Error', true);
    }
});