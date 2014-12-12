window.Pixel = new Class({
    x: 0,
    y: 0,

    __construct: function (x, y) {
        if (typeof x != 'undefined') {
            if (typeof y != 'undefined') {
                self.x = x || 0;
                self.y = y || 0;
            } else {
                self.x = x.x;
                self.y = x.y;
            }
        }
    },

    inside: function (rect) {
        return self.x >= rect.start.x && self.x <= rect.end.x && self.y >= rect.start.y && self.y <= rect.end.y;
    },
    
    toString: function () {
        return [self.x, self.y].toString();
    }
});

window.Rect = new Class({
    start: null,
    end: null,

    __construct: function (start, end) {
        if (start && end) {
            self.start = start;
            self.end = end;
        } else {
            self.start = new Pixel;
            self.end = new Pixel;
        }
    },
    
    toString: function () {
        return [self.start.toString(), self.end.toString()].toString();
    }
});

Rect.fromNode = function (node) {
    var start = node.coordinates();
    return new Rect(start, new Pixel(start.x + node.offsetWidth, start.y + node.offsetHeight));
}
