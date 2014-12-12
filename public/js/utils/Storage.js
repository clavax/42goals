window.Storage = new Class ({
    name: '',
    data: null,
    
    __construct: function (name) {
        self.name = name;
        
        // @todo: works in Chrome, maybe in other browsers, need to check
        try {
            var serialized = localStorage.getItem(name) || '[]';
            self.data = JSON.parse(serialized);
        } catch (e) {
            self.data = [];
        }
        
    },
    
    insert: function (row) {
        self.data.push(Object.clone(row));
        return self.data.length;
    },
    
    update: function (row, predicate) {
        var rows_id = self.find(predicate);
        if (!rows_id) {
            return null;
        }
        return self.updateByIndex(row, rows_id);
    },
    
    updateByIndex: function (row, index) {
        for (var i = 0; i < index.length; i ++) {
            foreach(row, function (key, value) {
                self.data[index[i]][key] = value ? value.valueOf() : null;
            });
        };
        
        return index;
    },
    
    remove: function (predicate) {
        var rows_id = self.find(predicate);
        if (!rows_id) {
            return null;
        }
        return self.removeByIndex(rows_id);
    },
    
    removeByIndex: function (index) {
        for (var i = 0; i < index.length; i ++) {
            delete(self.data[index[i]]);
        };
        
        return index;
    },
    
    select: function (cols, predicate, order) {
        if (predicate == undefined) {
            predicate = {};
        }
        var rows_id = self.find(predicate);
        var rows = [];
        if (cols == undefined || cols == '*') {
            for (var i = 0; i < rows_id.length; i ++) {
                rows.push(Object.clone(self.data[rows_id[i]]));
            };
        } else if (cols instanceof Array) { 
            for (var i = 0; i < rows_id.length; i ++) {
                var row = {};
                foreach(cols, function () {
                    row[this] = self.data[rows_id[i]][this];
                });
                rows.push(Object.clone(row));
            };
        } else {
            for (var i = 0; i < rows_id.length; i ++) {
                rows.push(self.data[rows_id[i]][cols]);
            };
        }
        if (order != undefined) {
            rows.sort(function (a, b) {
                a[order] > b[order];
            });
        }
        return rows;
    },
    
    find: function (predicate) {
        var id = [];
        for (var i = 0; i < self.data.length; i ++) {
            if (self.data[i] == undefined) {
                continue;
            }
            if (self.match(self.data[i], predicate)) {
                id.push(i);
            }
        }
        return id;
    },
    
    isAny: function (predicate) {
        for (var i = 0; i < self.data.length; i ++) {
            if (self.data[i] == undefined) {
                continue;
            }
            if (self.match(self.data[i], predicate)) {
                return true;
            }
        }
        return false;
    },
    
    match: function (row, predicate) {
        // @todo: add indexes
        var result = false;
        if (predicate instanceof Array) {
            // [p1, p2] == p1 or p2
            foreach(predicate, function (key) {
                result = self.match(row, this);
                return !result;
            });
        } else {
            // {a: b, c: d} == (a = b) and (c = d)
            result = true;
            foreach(predicate, function (key, value) {
                if (row[key] == null) {
                    result = false;
                } else {
                    var val = row[key].valueOf();
                    if (value instanceof Array) {
                        result = Array.find(value, val) !== false;
                    } else {
                        result = value == val;
                    }
                }
                return result;
            });
        }
        
        return result;
    },
    
    store: function () {
        var rows = [];
        for (var i = 0; i < self.data.length; i ++) {
            if (self.data[i] != undefined) {
                rows.push(self.data[i]);
            }
        }
        
        try {
            // @todo: works in Chrome, maybe in other browsers, need to check
            localStorage.setItem(self.name, JSON.stringify(rows));
        } catch (e) {
        }
    }
});