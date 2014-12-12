Mohawk.Utils.Timer = new Class({
    __construct: function () {
        self.start = new Date;
    },
    
    stop: function () {
        self.end = new Date;
        return self.end.getTime() - self.start.getTime();
    }
    
});