include('mohawk.UI.List');

window.Carousel = Mohawk.UI.List.extend({
    element_width: null, // determine automatically
    element_height: 400,
    node_width: 400,
    node_height: 330,
    current: 0,
    
    __construct: function (id, structure) {
        parent.__construct(id, structure);
        window.onresize = function () {
            self.setPositions();
        };
        window.onload = function () {
            self.setPositions();
        };
        self.EVENT_TURNED = id + '-turned';
    },
    
    setPositions: function () {
        var theta = 0;
//        var step = 2 * Math.PI / (self.element.childNodes.length + 1);
        var step = Math.PI / 2 / (self.element.childNodes.length - 2);
        self.cells = [];
        var len = self.element.childNodes.length;
        for (var i = 0; i < len; i ++) {
            self.cells.push(theta);
            var child = self.element.childNodes[i];
            self.setPosition(child, child.angle != undefined ? child.angle : theta);
            if (child.cell == undefined) {
                child.cell = i;
            }
            theta += step;
        }
    },
    
    setPosition: function (node, angle) {
        angle = (Math.PI * 2 + angle) % (Math.PI * 2);
        
        // init
        var max_x = self.element_width || self.element.offsetWidth - 24;
        var max_y = self.element_height || self.element.offsetHeight;
        
        var r_w = max_x;
        var r_h = 400;
        
        var w = self.node_width;
        var h = self.node_height;
        
        // set scale
        //var scale = angle < Math.PI ? Math.cos(angle / 2) : Math.sin((angle - Math.PI) / 2);

        //var scale = angle < Math.PI ? 1 - angle / Math.PI : angle / Math.PI - 1;
        //scale += 0.1;
        
        var max_a = Math.PI / 4;
        var scale = angle < max_a ? angle / max_a : 2 - angle / max_a;
//        scale = 1 / Math.pow(scale + 1, 2);
//        scale = Math.exp(-Math.pow(scale * 1.5, 2));
        
        w *= scale;
        h *= scale;
        
        var x = (r_w - w) / 2 * Math.sin(angle);
        var y = (r_h - h) / 2 * Math.cos(angle);
        
        // set center
        x += max_x / 2;
        y += max_y / 2;
        
        // notice node size
        x -= w / 2;
        y -= h / 2;
        
        node.style.left = x + 'px'; 
        node.style.top = y + 'px';
        
        node.style.width = w + 'px';
        node.style.height = h + 'px';
        
        node.firstChild.style.width = w + 'px';
        node.firstChild.style.height = h + 'px';
        
        self.setShadow(node.firstChild, w / 25);
        
        node.style.zIndex = Math.round(scale * 10);
        
        node.angle = angle;
        //node.title = Math.round(angle / 2 / Math.PI * 360);
    },
    
    setShadow: function (node, width) {
        var style = '0px ' + Math.round(width / 2) + 'px ' + Math.round(width) + 'px rgba(0, 0, 0, 0.3)';
        node.style.mozBoxShadow = style;
        node.style.boxShadow = style;
        node.style.webkitBoxShadow = style;
    },
    
    setNode: function (node) {
        var current = 0;
        for (var i = 0; i < self.element.childNodes.length; i ++) {
            if (node === self.element.childNodes[i]) {
                current = i;
                break;
            }
        }
        
        if (!node.cell) {
            return;
        }
        
        var clockwise = node.angle <= Math.PI;
        var gap = clockwise ? node.cell : self.element.childNodes.length - node.cell;

        var n = 0;
        
        var _turn = function () {
            self.turn(clockwise, function () {
                n ++;
                if (n == gap) {
                    self.current = current;
                    node.addClass('current');
                    Observer.fire(self.EVENT_TURNED, node);
                } else {
                    _turn();
                }
            });
        };
        _turn();
    },
    
    turn: function (clockwise, finalize) {
        var frames = 180;
        var steps_num = frames / self.element.childNodes.length;

        var len = self.element.childNodes.length;
        var gap = clockwise ? -1 : 1;
        
        var steps = [];
        for (var i = 0; i < len; i ++) {
            var child = self.element.childNodes[i];
            var next_num = (i + gap + len) % len;
            var next = self.element.childNodes[next_num];
            child.nextCell = next.cell;
            
            var step;
            if (clockwise) {
                if (child.cell > next.cell) {
                    step = child.angle - next.angle;
                } else {
                    step = child.angle - next.angle + 2 * Math.PI;
                }
            } else {
                if (child.cell < next.cell) {
                    step = child.angle - next.angle;
                } else {
                    step = child.angle - next.angle - 2 * Math.PI;
                }
            }
            step /= steps_num;
            
            steps.push(step);
        }
        
        var t = 0;
        self.element.addClass('moving');
        _turn = function () {
            t += 1;
            for (var i = 0; i < len; i ++) {
                var child = self.element.childNodes[i];
                self.setPosition(child, child.angle - steps[i]);
            }
            if (t < steps_num - 1) {
                setTimeout(_turn, 1);
            } else {
                self.element.removeClass('moving');
                
                for (var i = 0; i < len; i ++) {
                    var child = self.element.childNodes[i];
                    var next_cell = self.element.childNodes[child.nextCell].cell;
                    self.setPosition(child, self.cells[child.nextCell]);
                    child.cell = child.nextCell;
                    child.removeClass('current');
                }
                finalize();
            }
        };
        _turn();
    },
    
    createNode: function (data) {
        var node = parent.createNode(data);
        node.onclick = function () {
            if (self.element.hasClass('moving')) {
                return;
            }
            if (!node.cell) {
                return;
            }
            self.setNode(node);
        };
        
        return node;
    }
});