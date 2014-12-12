window.LocalSettings = new Singletone({
    Storage: null,
    
    __construct: function () {
        self.Storage = new PkStorage('local_settings', 'key');
        
        window.addEvent('unload', function () {
            LocalSettings.Storage.store();
        });
    },
    
    set: function (key, value) {
        if (self.Storage.isAnyByPk(key)) {
            self.Storage.update({value: value}, {key: key});
        } else {
            self.Storage.insert({key: key, value: value});
        }
    },
    
    get: function (key) {
        return self.Storage.selectByPk('value', key);
    }
});