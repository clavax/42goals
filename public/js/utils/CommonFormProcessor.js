include('utils.FormProcessor');

window.CommonFormProcessor = FormProcessor.extend({
    template: false,
    
    doAfterSubmit: function () {
        parent.doAfterSubmit();
        
        foreach(self.form.getElementsByClassName('error'), 
            function () {
                this.remove();
            }
        );
    },

    successHandler: function (req) {
        parent.successHandler(req);

        if (self.template) {
            Template.assign('data', req.data.item);
            var div = document.createElement('DIV');
            div.id = self.form.id;
            div.className = 'answer';
            div.innerHTML = Template.transform(self.template);
            self.form.parentNode.replaceChild(div, self.form);
        }
    },

    errorHandler: function (req) {
        parent.errorHandler(req);
        
        var error = req.data.error;

        foreach(error, 
            function (name) {
                var input = self.form['data[' + name + ']'];
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

        if (error.captcha && ID('captcha')) {
            var img = ID('captcha');
            var d = new Date;
            var uid = '' + d.getHours() + d.getMinutes() + d.getSeconds() + d.getMilliseconds();
            img.src = img.src.replace(new RegExp('(\\?\\d+)?$'), '?' + uid);
        }
    }
});