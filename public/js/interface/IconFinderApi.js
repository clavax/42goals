include('UI.IconList');
Loader.includeLanguage('goals');

window.IconFinderApi = new Class({
    List: null,

    initSearch: function (input) {
        clearTimeout(self.search_timeout);
        self.search_timeout = setTimeout(function () {
            self.search(input.value);
        }, 1000);
    },
    
    search: function (q, p) {
        p = p || 0;
        
        var req = new Ajax(URL.home + 'api/iconfinder/');
        req.responseHandler = function (req) {
            try {
                var icons = req.data.iconmatches.icon;
                if (icons.id) {
                    icons = [icons];
                }
                
                var data = [];
                foreach(icons, function (i, icon) {
                    data.push({
                        id: 'if-' + icon.id, 
                        src: unescape(icon.image)
                    });
                });
                if (!p) {
                    self.List.setChildren(data);
                } else {
                    foreach(data, function (i, icon) {
                        self.List.addNode(icon);
                    });
                }
                self.List.element.removeClass('hidden');

                var link = DOM.element('A');
                link.href = '#more';
                link.addClass('script');
                link.onclick = function () {
                    self.search(q, p + 1);
                    return false;
                };
                link.setHTML(LNG.Search_more);
                self.Message.setHTML('');
                self.Message.appendChild(link);
                self.Message.appendChild(DOM.text(' (' + LNG.powered_by_iconfinder + ')'));
            } catch (e) {
                // error :(
                self.Message.setHTML(LNG.Nothing_found);
            }
        };
        
        req.errorHandler = function (req) {
            Progress.done('Error');
        };
        
        req.send({q: q, p: p});
        if (!p) {
            self.List.element.addClass('hidden');
        }
        self.Message.removeClass('hidden');
        self.Message.setHTML(LNG.Searching + '...');
    }
});