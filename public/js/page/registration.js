include('utils.CommonFormProcessor');
Loader.includeTemplate('registration-success');
Loader.includeTemplate('registration-email');
Loader.includeTemplate('recovery-success');
Loader.includeLanguage('users');
Loader.includeLanguage('registration');

window.email_provider = function (email) {
    return 'http://' + email.substr(email.indexOf('@') + 1);
};

document.addLoader(function () {
    if (document.body.id != 'registration-page') {
        return;
    }

    window.Registration = new (CommonFormProcessor.extend ({
        template: REGISTRATION_SUCCESS,
        
        setLoginExample: function (input) {
            var value = input.value.toLowerCase();
            value = value.replace(/\W/g, '');
            ID('login-example').setHTML(value);
        }
    }));
});

document.addLoader(function () {
    if (document.body.id != 'recovery-page') {
        return;
    }
    
    window.Recovery = new (CommonFormProcessor.extend ({
        template: RECOVERY_SUCCESS
    }));    
});

document.addLoader(function () {
    if (document.body.id != 'confirmation-page') {
        return;
    }

    window.Confirmation = new (CommonFormProcessor.extend ({
        method: Ajax.METHOD_PUT,
        template: REGISTRATION_EMAIL
    }));
});