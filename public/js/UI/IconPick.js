include('UI.IconList');
include('interface.IconFinderApi');

window.IconPick = new Class({
    __construct: function (icons) {
        var element = DOM.element('DIV');
        element.addClass('icon-pick');
        self.element = element;

        self.icons = icons;
        
        var input = FormsInterface.createInput('text', 'q', LNG.search + '...');
        input.onchange = function () {
            if (!this.value.length) {
                self.setDefault();
            }
        };
        input.title = LNG.search + '...';
        input.addClass('empty');
        input.onfocus = function () {
            if (this.value == this.title) {
                this.value = ''; 
            }
            this.removeClass('empty');
        };
        input.onblur = function () {
            if (this.value == '') {
                this.value = this.title; 
                this.addClass('empty');
            }
        };
        input.onkeydown = function (event) {
            event = DOM.event(event);
            if (event.key() == 13) {
                return false;
            }
        };
        input.onkeyup = function () {
            self.IconFinder.initSearch(this);
        };
        input.value = input.title;
        self.input = input;
        
        element.appendChild(input);
        
        var hint = DOM.element('P');
        hint.addClass('hint');
        hint.setHTML(LNG.Icon_search_hint);
        element.appendChild(hint);

        self.IconList = new IconList('icon-list', icons);
        Observer.add(self.IconList.EVENT_SELECTED, function () {
            if (!self.IconList.selected.length) {
                return;
            }
            var data = self.IconList.selected[0].data;
            self.Picker.setData(data.id.match('^if-') ? data.src : data.id);
            self.hide();
        });
        element.appendChild(self.IconList.element);
        
        self.message = DOM.element('P');
        self.message.addClass('message', 'hidden');
        element.appendChild(self.message);

        self.IconFinder = new IconFinderApi();
        self.IconFinder.List = self.IconList;
        self.IconFinder.Message = self.message;
    },
    
    setDefault: function () {
        self.message.addClass('hidden');
        self.IconList.setChildren(self.icons);
    },
    
    set: function (picker) {
        if (self.Picker) {
            self.Picker.link.setHTML(LNG.Pick_icon);
        }
        self.Picker = picker;
        if (self.Picker) {
            self.Picker.link.setHTML(LNG.Hide_ok);
        }
        var node = self.IconList.getNode(picker.data);
        if (node) {
            self.IconList.select({}, node);
        } else {
            self.IconList.unselectAll();
            self.setDefault();
            self.input.value = '';
            setTimeout(function () {self.input.focus();});
        }
    },
    
    hide: function () {
        if (self.Picker) {
            self.Picker.link.setHTML(LNG.Pick_icon);
        }
        self.Picker = null;
        self.element.remove();
    }
});

window.IconPicker = new Class({
    Pick: null,
    data: null,
    
    __construct: function (Pick) {
        self.Pick = Pick;
        self.element = DOM.element('DIV');
        self.element.addClass('icon-picker');
        self.element.onclick = function () {
            self.Pick.set(self);
            self.element.parentNode.insertAfter(self.Pick.element, self.element);
        };
        
        self.img = DOM.element('IMG');
        self.element.appendChild(self.img);
        
        var link = DOM.element('A');
        link.href = '#icon-pick';
        link.addClass('script');
        link.setHTML(LNG.Pick_icon);
        link.onclick = function () {
            if (self.Pick.Picker === self) {
                setTimeout(function () {self.Pick.hide();}); // WTF? calling it normally doesn't work
            } else {
                self.Pick.set(self);
            }
            return false;
        };
        self.element.appendChild(link);
        self.link = link;
    },
    
    setData: function (data) {
        self.data = data;
        if (data && data != '0') {
            self.img.src = Data.icons[data] !== undefined ? Data.icons[data] : data;
        } else {
            self.img.src = URL.img + 'icons/loading.png';
        }
    }
});