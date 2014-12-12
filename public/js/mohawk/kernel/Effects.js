Mohawk.NodeEffectInterface = {
    show: function (effect) {
        if (this.style.visibility == 'hidden') {
            this.style.visibility = 'visible';
            if (effect instanceof Function) {
                effect.call(this, this, this.show);
            }
        }
    },

    hide: function (effect) {
        if (this.style.visibility != 'hidden') {
            if (effect instanceof Function) {
                effect.call(this, this, this.hide);
            } else {
                this.style.visibility = 'hidden';
            }
        }
    },

    display: function (effect, display) {
//        if (this.style.display == 'none') {
            this.style.display = display ? display : (this._display ? this._display : 'block');
            if (effect instanceof Function) {
                effect.call(this, this, this.display);
            }
//        }
    },

    collapse: function (effect) {
        if (this.style.display != 'none') {
            if (effect instanceof Function) {
                effect.call(this, this, this.collapse);
            } else {
                this._display = this.style.display;
                this.style.display = 'none';
            }
        }
    },

    toggleDisplay: function (effect) {
        if (e.offsetWidth == 0) {
            this.display(effect);
        } else {
            this.collapse(effect);
        }
    },

    remove: function (effect) {
        if (this.parentNode) {
            if (effect instanceof Function) {
                effect(this, this.remove);
            } else {
                this.parentNode.removeChild(this);
            }
        }
    },

    setOpacity: function (opacity) {
        if (IE) {
            if (this.tagName && this.tagName.toUpperCase() == 'IMG' && this.src.match(/\.png$/)) {
                this.style.filter = 'AlphaImageLoader(src="' + this.src + '", sizingMethod="scale") Alpha(Opacity="' + (opacity * 100) + '")';
            } else {
                this.style.filter = 'Alpha(Opacity="' + (opacity * 100) + '")';
            }
        } else {
            this.style.opacity = opacity;
        }
    },

    getOpacity: function (opacity) {
        if (IE) {
            // ???
        } else {
            return this.style.opacity;
        }
    }
};

Mohawk.Effects = new Singletone ({
    opacity_delay: 10,
    opacity_step: 0.025,
    blink_delay: 100,
    blink_number: 3,
    _stop: false,
    moving: null,

    opacity: function (object, start, end, finalize, current) {
        self = Mohawk.Effects;

        if (typeof current == 'undefined') {
            current = start;
            object._opacity = object.getOpacity();
            if (IE) {
                object._filter = object.style.filter;
            }
        }

        current += self.opacity_step * Math.sign(end - start);
        object.setOpacity(current);

        if (Math.sign(end - start) != 0 && (Math.sign(end - start) == Math.sign(end - current) || !Math.sign(end - current))) {
            setTimeout(function() {self.opacity(object, start, end, finalize, current);}, self.opacity_delay);
        } else {
            object.setOpacity(end);
            if (finalize instanceof Function) {
                finalize.call(object);
            }
            object.setOpacity(object._opacity);
            if (IE) {
                object.style.filter = object._filter;
            }
        }
    },

    appear: function (object, finalize) {
        Mohawk.Effects.opacity(object, 0, 1, finalize);
    },

    vanish: function (object, finalize) {
    	Mohawk.Effects.opacity(object, 1, 0, finalize);
    },
    
    fold: function (object, finalize) {
        var height = object.offsetHeight;
        var step = Math.floor(height / 48);
        var _fold = function () {
            if (height < step || step <= 0) {
                object.style.height = '0px';
                // finalize
                if (finalize instanceof Function) {
                    finalize.call();
                }
                object.style.overflow = 'auto';
                object.style.height = 'auto';
            } else {
                height -= step;
                object.style.height = height + 'px';
                setTimeout(_fold, 1);
            }
        };
        object.style.overflow = 'hidden';
        _fold();
    },
    
    unfold: function (object, finalize) {
        var height = 0;
        var end = object.offsetHeight;
        var step = Math.floor(end / 48);
        var _unfold = function () {
            if (end - height < step || step <= 0) {
                // finalize
                if (finalize instanceof Function) {
                    finalize.call();
                }
                object.style.overflow = 'auto';
                object.style.height = 'auto';
            } else {
                height += step;
                object.style.height = height + 'px';
                setTimeout(_unfold, 1);
            }
        };
        object.style.overflow = 'hidden';
        object.style.height = '0px';
        _unfold();
    },
    
    strike: function (node, finalize) {
        var html = node.innerHTML;
        var text = node.textContent || node.innerText;
        var del = DOM.element('DEL');
        var span = DOM.element('SPAN');
        var len = Math.ceil(text.length / 5);
        var _strike = function () {
            if (text.length <= len) {
                if (finalize instanceof Function) {
                    finalize.call();
                }
                node.setHTML(html);
            } else {
                var str = text.substr(0, len);
                text = text.substr(len);
                del.innerHTML += str;
                span.innerHTML = text;
                setTimeout(_strike, 100);
            }
        };
        node.removeChildren();
        node.appendChild(del);
        node.appendChild(span);
        _strike();
    },

    blink: function (object, finalize, start, counter) {
        self = Mohawk.Effects;

        if (typeof counter == 'undefined') {
            counter = 0;
            start = Array.find(['hidden', 'off'], object.style.visibility) !== false;
        }
        counter ++;

        if (counter < self.blink_number * 2) {
            object.style.visibility = (counter + start) % 2 ? 'hidden' : 'visible';
            setTimeout(function() {self.blink(object, finalize, start, counter)}, self.blink_delay);
        } else {
            object.style.visibility = start ? 'visible' : 'hidden';
            if (finalize instanceof Function) {
                finalize.call(object);
            }
        }
    },

    move: function (e, x, y, step_x, step_y, acc_x, acc_y, finalize, offset) {
        var self = Mohawk.Effects;

        var cur_x = e.offsetLeft;
        var cur_y = e.offsetTop;
        
        if (!offset) {
            var temp = document.createElement('DIV');
            temp.style.left = '0px';
            temp.style.top = '0px';
            temp.style.position = 'absolute';
            e.parentNode.appendChild(temp);
            offset = new Pixel(temp.offsetLeft, temp.offsetTop);
            temp.remove();
        }

        if (!x && x !== 0) {
            x = cur_x;
        }
        if (!y && y !== 0) {
            y = cur_y;
        }
        if (!step_x) {
            step_x = 1;
        }
        if (!step_y) {
            step_y = 1;
        }
        if (!acc_x) {
            acc_x = 1;
        }
        if (!acc_y) {
            acc_y = 1;
        }
        
        self._move(e, x, y, step_x, step_y, acc_x, acc_y, finalize, offset);
    },
    
    _move: function (e, x, y, step_x, step_y, acc_x, acc_y, finalize, offset) {
        var self = Mohawk.Effects;

        var cur_x = e.offsetLeft;
        var cur_y = e.offsetTop;
        
        var moved_x = false;
        if (Math.abs(cur_x - x) >= step_x) {
            e.style.left = (cur_x + Math.sign(x - cur_x) * step_x) + 'px';
            moved_x = true;
        } else {
            e.style.left = x - offset.x + 'px';
        }

        var moved_y = false;
        if (Math.abs(cur_y - y) >= step_y) {
            e.style.top = (cur_y + Math.sign(y - cur_y) * step_y) + 'px';
            moved_y = true;
        } else {
            e.style.top = y - offset.y + 'px';
        }

        if (moved_x || moved_y) {
            self.moving = setTimeout(function() {self._move(e, x, y, step_x * acc_x, step_y * acc_y, acc_x, acc_y, finalize, offset);}, 1);
        } else {
            if (finalize instanceof Function) {
                finalize.call(e);
            }
        }        
    },
    
    stop: function () {
        clearTimeout(self.moving);
    },
    
    scrollTo: function (obj) {
        var to = obj.coordinates().y;
        var from = document.scrollTop();
        var step = Math.abs(to - from) / 10;
        var prev = 0;
        var scroll = function () {
            var now = document.scrollTop();
            if (now == prev) {
                return;
            }
            if (Math.abs(to - now) <= step) {
                window.scrollTo(0, to);
            } else {
                window.scrollBy(0, Math.sign(to - now) * step);
                setTimeout(scroll, 1);
            }
            prev = now;
        };
        scroll();
    }
});