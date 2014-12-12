window.AnchorObserver = new Singletone ({
	timeout: 10,
	current: '',
    events: {},
    
	__construct: function () {
    	self.events = {}; // possibly a bug? i have to assign values here
    	self.timeout = 500;
	},
	
    add: function (pattern, action) {
        self.events[pattern] = action;
    },
    
    start: function () {
    	self.current = window.location.hash.substring(1)
    	self.fire();
    	self.test();
    },
    
    test: function () {
    	var hash = window.location.hash.substring(1);
    	if (hash != self.current) {
    		self.current = hash;
    		self.fire();
    	}
    	setTimeout(function () {self.test.apply(self);}, self.timeout);
    },
    
    fire: function () {
		foreach(self.events, function (pattern, event) {
			var m = RegExp(pattern).exec(self.current);
			if (m) {
				m.shift();
				event.apply(null, m);
				return false;
			}
		});
    }
});