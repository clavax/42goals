include('utils.CommonFormProcessor');

Loader.includeTemplate('delete-account');

Loader.includeLanguage('users');
Loader.includeLanguage('settings');

document.addLoader(function () {
    if (document.body.id != 'settings-page') {
        return;
    }
    
    window.Settings = new (CommonFormProcessor.extend ({
        template: false,
        
        successHandler: function (req) {
            try {
                var new_data = req.data.item;
    
                foreach(new_data, function (key) {
                    ENV.user[key] = this;
                });
            } catch(e) {
                Console.describe(e);
            }
            parent.successHandler(req);
        }
    }));
});

document.addLoader(function () {
    if (document.body.id != 'delete-page') {
        return;
    }

    window.DeleteAccount = new (CommonFormProcessor.extend ({
        method: Ajax.METHOD_DELETE,
        template: DELETE_ACCOUNT
    }));
});