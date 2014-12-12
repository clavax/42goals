Mohawk.Resize = new Singletone({
    objects: [],
    increment: {x: 0, y: 0},
    grab: {top: 5, right: 5, bottom: 5, left: 5},
    css_class: 'resizable',
    
    indicate: function (event, element) {
    	event = Mohawk.DOM.event(event);

        var cur = event.cursor();
        var elem = element.coordinates();
        
        if (element.offsetWidth - (cur.x - elem.x) < self.grab.right && element.offsetHeight - (cur.y - elem.y) < self.grab.bottom) {
            element.style.cursor = 'se-resize'; // \
        } else if ((cur.x - elem.x) && (cur.x - elem.x) < self.grab.left && (element.offsetHeight - (cur.y - elem.y)) < self.grab.bottom) {
            element.style.cursor = 'sw-resize'; // /
        } else if ((cur.x - elem.x) && (cur.x - elem.x) < self.grab.left) {
            element.style.cursor = 'w-resize'; // <-
        } else if (element.offsetWidth - (cur.x - elem.x) < self.grab.right) {
            element.style.cursor = 'e-resize'; // ->
        } else if (element.offsetHeight - (cur.y - elem.y) < self.grab.bottom) {
            element.style.cursor = 's-resize'; // |
        } else {
            element.style.cursor = 'default';
        }
    },
    
    createResizeObject: function (node) {
        return {
            node: node,
            width: node.style.width ? parseInt(node.style.width) : node.offsetWidth,
            height: node.style.height ? parseInt(node.style.height) : node.offsetHeight,
            factor: {
                x: node.getAttribute('resizex') ? parseFloat(node.getAttribute('resizex')) : 1,
                y: node.getAttribute('resizey') ? parseFloat(node.getAttribute('resizey')) : 1
            }
        };
    },
    
    init: function (event, element) {
    	event = Mohawk.DOM.event(event);
    	
        if (event.target != element) {
            return false;
        }
        
        var cur = event.cursor();
        var elem = element.coordinates();

        if (element.offsetWidth - (cur.x - elem.x) < self.grab.right && element.offsetHeight - (cur.y - elem.y) < self.grab.bottom) {
            self.increment = {x: 1, y: 1} // \;
        } else if ((cur.x - elem.x) && (cur.x - elem.x) < self.grab.left && (element.offsetHeight - (cur.y - elem.y)) < self.grab.bottom) {
            self.increment = {x: -1, y: 1}; // /
        } else if ((cur.x - elem.x) && (cur.x - elem.x) < self.grab.left) {
            self.increment = {x: -1, y: 0} // <-;
        } else if (element.offsetWidth - (cur.x - elem.x) < self.grab.right) {
            self.increment = {x: 1, y: 0}; // ->
        } else if (element.offsetHeight - (cur.y - elem.y) < self.grab.bottom) {
            self.increment = {x: 0, y: 1}; // v
        } else {
            self.increment = {x: 0, y: 0};
        }

        if (self.increment.x || self.increment.y) {
            self.offset = {x: cur.x, y: cur.y};

            self.objects.push(self.createResizeObject(element));

            var descendants = element.getElementsByTagName('*');
            for (var i = 0; i < descendants.length; i ++) {
                var resize_element = descendants[i];
                if (resize_element.hasClass(self.css_class)) {
                    self.objects.push(self.createResizeObject(resize_element));
                }
            }

            document.addEvent('mousemove', Resize.start);
            document.addEvent('mouseup', Resize.stop);
        }
    },
    
    start: function (event) {
        self = Mohawk.Resize;
        
        event = Mohawk.DOM.event(event);
        
        var cur = event.cursor();
        
        for (var i = 0; i < self.objects.length; i ++) {
            var element = self.objects[i];
            var elem = element.node.coordinates();

            var resize_width  = element.width + (cur.x - self.offset.x) * self.increment.x * element.factor.x;
            var resize_left   = self.increment.x < 0 ? cur.x : elem.x;
            var resize_height = element.height + (cur.y - self.offset.y) * self.increment.y * element.factor.y;
            var resize_top    = self.increment.y < 0 ? cur.y : elem.y;

            if (resize_width >= element.node.style.minWidth || true) {
                element.node.style.width = resize_width + 'px';
                element.node.style.left = resize_left + 'px';
            }

            if (resize_height >= element.node.style.minHeight || true) {
                element.node.style.height = resize_height + 'px';
                element.node.style.top = resize_top + 'px';
            }
        }
    },

    stop: function (event) {
        self = Mohawk.Resize;

        event = Mohawk.DOM.event(event);
        
        document.removeEvent('mousemove', Resize.start);
        document.removeEvent('mouseup', Resize.stop);
        self.reset();
    },
    
    reset: function () {
        self.objects = [],
        self.increment = {x: 0, y: 0};
    }
        
});

window.Resize = Mohawk.Resize;