include('utils.CommonFormProcessor');
Loader.includeLanguage('users');
Loader.includeLanguage('api');
Loader.includeTemplate('app-added');

document.addLoader(function () {
    if (document.body.id != 'apps-page') {
        return;
    }

    window.Apps = new (CommonFormProcessor.extend ({
        successHandler: function (req) {
            if (req.data.item.appkey) {
                self.template = APP_ADDED;
            }
            parent.successHandler(req);
        }
    }));
});