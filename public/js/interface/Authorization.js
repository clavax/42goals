include('mohawk.kernel.Ajax');
Loader.includeLanguage('login');

window.Authorization = new Class ({
    login: function (form) {
        var req = new Mohawk.Ajax(URL.home + 'api/login/', Mohawk.Ajax.METHOD_POST);
        req.responseHandler = function (req) {
            if (req.data.ok) {
                Progress.load(LNG.Logged_in);
                if (req.data.remember) {
                    window.location = URL.sso + 'setcookie/' + req.data.sid + '/?redirect=' + escape(URL.host + form.getData().redirect);
                } else {
                    window.location = URL.sso + 'setsession/' + req.data.sid + '/?redirect=' + escape(URL.host + form.getData().redirect);
                }
            } else {
                Progress.done(LNG.Error_login);
            }
        };
        req.send(form.getData());
        Progress.load(LNG.Logging_in);
    },

    logout: function (sid) {
//        if (!confirm(LNG.Confirm_logout)) {
//            return;
//        }
        if (ENV.SID) {
            var url = URL.sso + 'deletecookie/' + ENV.SID + '/?redirect=' + escape(window.location);
            window.location = URL.home + 'api/login/deletecookie/' + ENV.SID + '/?redirect=' + escape(url);
        } else {
            var req = new Mohawk.Ajax(URL.home + 'api/login/', Mohawk.Ajax.METHOD_DELETE);
            req.responseHandler = function (req) {
                Progress.load(LNG.Logged_out);
                window.location.reload();
            };
            req.send();
            Progress.load(LNG.Logging_out);
        }
    }
});