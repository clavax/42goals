window.GoalsNumber = new Class({
    element: null,
    input: null,
    
    __construct: function () {
        var element = DOM.element('DIV');
        element.id = 'goals-number';
        element.addClass('goals-number-input', 'rounded');
        element.onclick = DOM.stopEvent;
        self.element = element;
        
        var corner1 = DOM.element('SPAN');
        corner1.addClass('corner-border');
        element.appendChild(corner1);
        
        var corner2 = DOM.element('SPAN');
        corner2.addClass('corner-bg');
        element.appendChild(corner2);
        
        var input = DOM.element('input', {type: 'text'});
        input.id = self.id + '-input';

        var mode = DOM.element('LABEL');
        mode.htmlFor = input.id;
        mode.setHTML('+');
        mode.onclick = function () {
            if (mode.innerHTML == '+') {
                mode.setHTML('-');
            } else if (mode.innerHTML == '-') {
                mode.setHTML('=');
            } else {
                mode.setHTML('+');
            }
        };
        element.appendChild(mode);
        self.mode = mode;
        
        input.onclick = DOM.stopEvent;
        input.onmousedown = DOM.stopEvent;
        input.onkeypress = function (event) {
            event = DOM.event(event);
            if (input.value.match(/^=/)) {
                self.mode.innerHTML = '=';
                self.input.value = input.value.replace(/^=/, '');
            } else if (input.value.match(/^-/)) {
                if (self.mode.innerHTML != '=') {
                    self.mode.innerHTML = '-';
                    self.input.value = input.value.replace(/^-/, '');
                }
            } else if (input.value.match(/^\+/)) {
                self.mode.innerHTML = '+';
                self.input.value = input.value.replace(/^\+/, '');
            }
            if(document.all){
                if (event.key() == 13) {
                    self.save();
                    self.hide();
                }
            }else{
                if (event.keyCode == 13) {
                        self.save();
                        self.hide();
                    }
            
            }
            
            
        };
        element.appendChild(input);
        self.input = input;
        
        var close = DOM.element('IMG');
        close.src = URL.img + 'site/enter.png';
        close.onclick = function () {
            self.save();
            self.hide();
        };
        close.addClass('close');
        element.appendChild(close);
        
        var clear = DOM.element('IMG');
        clear.src = URL.img + 'site/trash.png';
        clear.onclick = function () {
            self.clear();
            self.hide();
        };
        clear.addClass('clear');
        element.appendChild(clear);
        
        document._toHideOnClick.push(self);
    },
    
    save: function () {
        var value = parseFloat(self.cell.value);
        if (isNaN(value)) {
            value = 0;
        }
        var input = parseFloat(self.input.value);
        if (isNaN(input)) {
            return;
        }
        if (self.mode.innerHTML == '+') {
            value += input;
        } else if (self.mode.innerHTML == '-') {
            value -= input;
        } else {
            value = input;
        }
        value = Math.round(value, 5);
        self.cell.setValue(value);
        Goals.save(self.cell.row.data.id, Format.date(self.cell.col.data.date, 'Y-m-d'), {value: value});
    },
    
    clear: function () {
        self.cell.setValue('');
        Goals.save(self.cell.row.data.id, Format.date(self.cell.col.data.date, 'Y-m-d'), {value: ''});
    },

    set: function (cell) {
        document.body.appendChild(self.element);
        
        self.element.alignTo(cell, 'middle', true);
        self.element.adjoinTo(cell, 'right', true);
        
        self.cell = cell;
        self.mode.innerHTML = '+';
        self.input.value = '';
        self.input.focus();
        
        Goals.Comment.hide();
    },
    
    hide: function () {
        self.element.remove();
    }    
});