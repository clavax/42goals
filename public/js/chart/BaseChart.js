window.BaseChart = new Class({
    width: 0,
    height: 0,
    canvas: null,
    type: null,
    interpolate: false,
    title: '',
    
    set: function (options) {
        var fields = ['width', 'height', 'canvas', 'type', 'data', 'interpolate'];
        foreach(fields, function (i, field) {
            if (field in options) {
                self[field] = options[field];
            }
        });
    },
    
    draw: function () {
        // fill here
    }
});