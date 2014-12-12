window.Uploader = new Class({
    id: '',
    frame: null,
    input: null,

    __construct: function (element) {
        self.frame = DOM.element('iframe', {
            id: element.id,
            src: URL.home + 'system/upload/',
            className: 'file-upload-frame',
            //onload: self.onload, // doesn't work in IE
            allowTransparency: true,
            scrolling: 'no',
            frameBorder: 0
        });
        self.frame.addEvent('load', self.onload);
        self.frame.object = self;
        element.replace(self.frame);
    },

    onload: function () {
        var frame = window.event ? window.event.srcElement : this;
        var win = frame.contentWindow;
        
        if (win.data) {
            frame.object.onUpload(win.data);
        }
    },
    
    onUpload: function () {
        // fill here
    }
});