include('mohawk.UI.OrderedList');
Loader.includeLanguage('goals');

window.TemplatesPick = Mohawk.UI.OrderedList.extend({
    active: null,
    
    createNode: function(data) {
        var node = parent.createNode(data);
        node.setHTML('');
        
        var title = DOM.element('H2');
        node.appendChild(title);
        
        var icon = DOM.element('IMG');
        if (data.type == 'boolean') {
        	icon.src = Data.icons[data.icon_true];
        } else if (data.type == 'counter') {
        	icon.src = Data.icons[data.icon_item];
        } else if (data.type == 'numeric') {
        	icon.src = URL.img + 'icons/number.png';
        } else if (data.type == 'time') {
        	icon.src = URL.img + 'icons/time.png';
        } else if (data.type == 'timer') {
        	icon.src = URL.img + 'icons/stopwatch.png';
        } else {
        	icon.src = URL.img + 'icons/loading.png';
        }
        title.insertFirst(icon);
        
        var span = DOM.element('SPAN');
        span.setHTML(data.title);
        title.appendChild(span);
        
        var text = DOM.element('DIV');
        text.addClass('text', 'hidden');
        
        var html = data.preview;
        //html = htmlspecialchars(html);
        //html = html.replace(/(http:\/\/[^\s\)]+)/img, '<a href="$1">$1</a>');
        html = nl2br(html);
        
        text.setHTML('<p>' + html + '</p>');
        node.appendChild(text);
        
        span.onclick = function () {
            if (text.hasClass('hidden')) {
                if (self.active) {
                    Effects.fold(self.active, function () {
                        self.active.addClass('hidden');
                        text.removeClass('hidden');
                        Effects.unfold(text);
                        self.active = text;
                    });
                } else {
                    text.removeClass('hidden');
                    Effects.unfold(text);
                    self.active = text;
                }
            } else {
                Effects.fold(text, function () {
                    text.addClass('hidden');
                    self.active = null;
                });
            }
        };
        var button = DOM.element('BUTTON', {type: 'button'});
        if (data.type != 'timer' || ENV.user.valid) {
            button.setHTML(LNG.Add_this_template);
            button.onclick = function () {
                var goal = Object.clone(data);
                goal.id = '';
                goal.tab = ENV.tab;
                goal.template = data.id;
                goal.position = (Goals.Table.table.tBodies[0].childNodes.length ? parseInt(Goals.Table.table.tBodies[0].lastChild.data.position) : 0) + 1;
                Goals.add(goal);
                return false;
            };
        } else {
            button.setHTML('<img src="' + URL.img + 'site/premium.png" alt="*" /> ' + LNG.Add_this_template);
            button.onclick = show_premium;
        }
        
        text.appendChild(button);

        return node;
    }
});