include('utils.PkStorage');
include('utils.Revisions');

window.StorageList = {};

window.RStorage = PkStorage.extend({
    __construct: function (name, pk) {
        self.EVENT_PK_UPDATED = 'pk-updated-' + name;
        self.EVENT_ROW_REMOVED = 'row-removed-' + name;
        
        parent.__construct(name, pk);
        window.StorageList[name] = self;
    },
    
    foreignKey: function (field, table) {
        Observer.add(table.EVENT_PK_UPDATED, function (tmp_id, new_id) {
            var data = {};
            data[field] = new_id;
            var cond = {};
            cond[field] = tmp_id;
            self.updateByIndex(data, self.find(cond));
        });

        Observer.add(table.EVENT_ROW_REMOVED, function (id, rev) {
            var cond = {};
            cond[field] = id;
            self.remove(cond, rev);
        });
    },
    
    insert: function (row, rev) {
        var id = parent.insert(row);
        
        var meta = {
            type: self.name,
            id: row[self.pk],
            r: 0,
            a: '+'
        };
        if (rev) {
            meta.a = 0;
            meta.r = rev;
        }
        Revisions.insert(meta);
        
        return id;
    },
    
    update: function (row, predicate, rev) {
        var rows_id = self.find(predicate);
        var meta_data = {a: 1};
        if (rev) {
            meta_data.a = 0;
            meta_data.r = rev;
        }
        for (var i = 0; i < rows_id.length; i ++) {
            var cond = {type: self.name, id: self.data[rows_id[i]][self.pk]};
            var meta = Revisions.select('a', cond);
            if (meta[0] == 0 || rev) {
                Revisions.update(meta_data, cond);
            }
        };
        
        return self.updateByIndex(row, rows_id);
    },

    updateByPk: function (data, id, rev) {
        var cond = {};
        cond[self.pk] = id;
        return self.update(data, cond, rev);
    },
        
    remove: function (predicate, rev) {
        var rows_id = self.find(predicate);
        var id_set = [];
        for (var i = 0; i < rows_id.length; i ++) {
            var id = self.data[rows_id[i]][self.pk];
            var cond = {type: self.name, id: id};
            var meta = Revisions.select('a', cond);
            if (meta[0] == '+') {
                Revisions.remove(cond);
            } else {
                Revisions.update({a: '-'}, cond);
            }
            id_set.push(id);
        }
        var result = self.removeByIndex(rows_id);
        if (rows_id.length) {
            Observer.fire(self.EVENT_ROW_REMOVED, id_set, rev);
        }
        return result;
    },

    removeByPk: function (id, rev) {
        var cond = {};
        cond[self.pk] = id;
        var result = self.remove(cond);
        if (rev) {
            self.apply(id, rev);
        }
        
        return result;
    },
    
    updatePk: function (tmp_id, new_id) {
        Revisions.update({id: new_id}, {type: self.name, id: tmp_id});
        var cond = {};
        cond[self.pk] = tmp_id;
        var data = {};
        data[self.pk] = new_id;
        self.parent.update(data, cond);
        Observer.fire(self.EVENT_PK_UPDATED, tmp_id, new_id);
    },
    
    apply: function (id, rev) {
        var cond = {type: self.name, id: id};
        var meta = Revisions.select('a', cond);
        try {
            if (meta[0] == '-') {
                Revisions.remove(cond);
            } else {
                Revisions.update({a: 0, r: rev}, cond);
            }
        } catch (e) {
            Console.describe(e);
        }
    },
    
    store: function () {
        parent.store();
        Revisions.store(); // @todo: might be redundant
    }
});