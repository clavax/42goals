window.Shadow = new Singletone({
    element: null,
    
    __construct: function () {
        var shadow = document.createElement('DIV');
        shadow.addClass('hidden');
        shadow.id = 'shadow';
        self.element = shadow;
    },
    
    init: function () {
        document.body.appendChild(self.element);
    },
    
    hide: function () {
        self.element.addClass('hidden');
    },
    
    show: function () {
        self.element.removeClass('hidden');
        self.element.style.height = (document.body.scrollHeight || document.documentElement.scrollHeight) + 'px';
        window.scrollTo(0, 0);
        //Effects.appear(self.element);
        Dragdrop.bringToFront(self.element);
    }
});