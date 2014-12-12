include('utils.Storage');

window.PkStorage = Storage.extend({
    pk: '',
    
    __construct: function(name, pk) {
        self.pk = pk;
        
        parent.__construct(name);
    },
    
    isAnyByPk: function (id) {
        var cond = {};
        cond[self.pk] = id;
        return self.isAny(cond);
    },
    
    removeByPk: function (id) {
        var cond = {};
        cond[self.pk] = id;
        self.remove(cond);
    },
    
    selectByPk: function(fields, id) {
        // @todo: improve performance
        var cond = {};
        cond[self.pk] = id;
        var rows = self.select(fields, cond);
        if (!rows) {
            return null;
        }
        return rows[0];
    }
});