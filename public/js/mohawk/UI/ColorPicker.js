Mohawk.Loader.addCss('color-picker.css');

Mohawk.UI.ColorPicker = new Class({
    element: null,
    indicator: null,
    input: null,
    color: false,
    
    __construct: function (colorset) {
        self.element = document.createElement('TABLE');
        self.element.addClass('color-picker');
        self.hide();
        
        if (!(colorset[0] instanceof Array)) {
            colorset = [colorset];
        }
        for (var i = 0; i < colorset.length; i ++) {
            var row = self.element.insertRow(self.element.rows.length);
            for (var j = 0; j < colorset[i].length; j ++) {
                var cell = row.insertCell(row.cells.length);
                cell.value = colorset[i][j];
                cell.object = self;
                cell.style.backgroundColor = '#' + colorset[i][j];
                cell.onmouseover = function () {
                    this.addClass('over');
                };
                cell.onmouseout = function () {
                    this.removeClass('over');
                };
                cell.onmousedown = function () {
                    self.input.value = this.value;
                    self.input.onchange();
                    self.hide();
                };
                cell.title = cell.value;
            }
        }
        
        self.indicator = document.createElement('SPAN');
        self.indicator.setHTML('&nbsp;');
        self.indicator.addClass('color-indicator');
    },
    
    set: function (input) {
        self.input = input;
        input.onfocus = function (event) {
            self.show();
        };
        input.onclick = function (event) {
            event = Mohawk.DOM.event(event);
            event.stopPropagation();
            return false;
        };
        input.onblur = function () {
            self.hide();
        };
        input.onchange = function () {
            if (input.value.length) {
                //this.style.border = '1px solid #' + this.value;
                self.indicator.style.backgroundColor = '#' + this.value;
            }
        };
        self.indicator.onclick = function (event) {
            self.show();
            
            event = Mohawk.DOM.event(event);
            event.stopPropagation();
            return false;
        };
        input.onchange();
    },
    
    show: function () {
        if (document._ObjectToHide && document._ObjectToHide != self) {
            document._ObjectToHide.doc_hide();
        }
        document._ObjectToHide = self;

        self.element.display();
        var x = self.indicator.coordinates().x;
        if (document.size().width < x + self.element.offsetWidth) {
            x -= self.element.offsetWidth - self.indicator.offsetWidth;
        }
        self.element.style.left = x + 'px';
        self.element.style.top = self.indicator.coordinates().y + self.indicator.offsetHeight + 'px';
        Dragdrop.bringToFront(self.element);
        
        document.addEvent('click', self.doc_hide);
    },
    
    hide: function () {
        self.element.collapse();
    },

    doc_hide: function () {
        document._ObjectToHide.hide();
        document.removeEvent('click', document._ObjectToHide.doc_hide);
    },
    
    append: function (element) {
        (element || document.body).appendChild(self.element);
        self.input.parentNode.insertAfter(self.indicator, self.input);
    }

});

Mohawk.UI.ColorPicker.generate = function (n) {
    var colorset = [];
    var color = [];
    for (var r = -1; r < 256; r += n) {
        var row = [];
        color[0] = (r > 0 ? r : 0).toHex().pad(2, '0', false);
        for (var g = -1; g < 256; g += n) {
            color[1] = (g > 0 ? g : 0).toHex().pad(2, '0', false);
            for (var b = -1; b < 256; b += n) {
                color[2] = (b > 0 ? b : 0).toHex().pad(2, '0', false);
                row.push(color.join(''));
            }
        }
        colorset.push(row);
    }
    return colorset;
};

Mohawk.UI.ColorPicker.set = function (input, colors) {
    var picker = new Mohawk.UI.ColorPicker(colors);
    picker.set(input);
    picker.append();
    return picker;
};