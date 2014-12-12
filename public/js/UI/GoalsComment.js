window.GoalsComment = new Class({
    element: null,
    
    __construct: function () {
        var element = DOM.element('DIV');
        element.id = 'goals-comment';
        element.addClass('rounded');
        element.onclick = DOM.stopEvent;
        self.element = element;
        
        var corner1 = DOM.element('SPAN');
        corner1.addClass('corner-border');
        element.appendChild(corner1);
        
        var corner2 = DOM.element('SPAN');
        corner2.addClass('corner-bg');
        element.appendChild(corner2);
        
        var close = DOM.element('IMG');
        close.src = URL.img + 'site/ok.png';
        close.onclick = function () {
            self.hide();
        };
        close.addClass('close');
        element.appendChild(close);
        
        var textarea = FormsInterface.createInput('textarea', 'text', '');
        element.appendChild(textarea);
        self.textarea = textarea;
        var text = 'Enter your comment here';
        textarea.onclick = DOM.stopEvent;
        textarea.onmousedown = DOM.stopEvent;
        textarea.onchange = function () {
            var date = Format.date(self.cell.col.data.date, 'Y-m-d');
            Goals.save(self.cell.row.data.id, date, {text: this.value});
        };
        
        document._toHideOnClick.push(self);
    },
    
    set: function (cell) {
        document.body.appendChild(self.element);
        
        var date = Format.date(cell.col.data.date, 'Y-m-d');
        self.textarea.value = Goals.getData(cell.row.data.id, date).text || '';
        self.cell = cell;
        
        self.element.adjoinTo(cell, 'bottom', true);
        self.element.alignTo(cell, 'left', true);

        self.element.addClass('left', 'top');
        self.textarea.focus();
        
        Goals.Number.hide();
    },
    
    hide: function () {
        self.element.remove();
    }
});