Mohawk.Observer = new Singletone ({
    events: {},
    
    add: function (type, action) {
        if (!(self.events[type] instanceof Array)) {
            self.events[type] = [];
        }
        self.events[type].push(action);
    },
    
    remove: function (type, action) {
        if (self.events[type] instanceof Array) {
            var source = action.toString();
            var i = 0;
            while (i < self.events[type].length) {
                if (self.events[type][i].toString() == source) {
                    self.events[type].splice(i, 1);
                } else {
                    i ++;
                }
            }
        }
    },

    fire: function (type) {
        if (!self.events[type]) {
            return;
        }
        var args = [];
        for (i = 1; i < arguments.length; i ++) {
            args.push(arguments[i]);
        }
        if (self.events[type] instanceof Array) {
            foreach(self.events[type], function () {
                this.apply(null, args);
            });
        }
    }
});