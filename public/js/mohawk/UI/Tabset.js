//Mohawk.Loader.addCss('tabset.css');

Mohawk.UI.Tabset = new Class ({
    element: null,
    current: null,
    
    tabs: [],

    __construct: function (tabs, attributes) {
        self.element = document.createElement('DL');
               
        if (!IE && attributes && attributes.length) { // @todo
            for (var i = 0; i < attributes.length; i ++) {
                self.element.setAttribute(attributes[i].nodeName, attributes[i].nodeValue);
            }
        }

        self.element.addClass.call(self.element, 'tabset');
        self.element.object = self;

        var max_height = 0;
        
        self.tabs = [];
        for (var i = 0; i < tabs.length; i ++) {
            var tab = {
                title: self.createTabTitle(i, tabs[i].title),
                content: self.createTabContent(i, tabs[i].content)
            };
            self.tabs.push(tab);
            
            if (i) {
                self.element.insertBefore(tab.title, self.tabs[i - 1].title.nextTag('DD'));
            } else {
                self.element.appendChild(tab.title);
            }
            self.element.appendChild(tab.content);

            /*if (max_height < self.tabs[i].content.offsetHeight) {
                max_height = self.tabs[i].content.offsetHeight;
            }*/
            tab.content.collapse();
        }
        
        /*for (var i = 0; i < tabs.length; i ++) {
            self.tabs[i].content.style.height = max_height + 'px';
        }*/
        
        self.tabs[0].title.open();
    },
    
    createTabTitle: function (index, title) {
        var node = document.createElement('DT');
        node.setHTML(title);
        node.addClass('tab');
        
        node.data = {
            index: index,
            title: title
        };
        
        node.getContent = function () {
            return self.tabs[node.data.index].content;
        };
        
        node.onclick = function () {
            node.open();
        };
        
        node.open = function () {
            if (self.current) {
                self.current.close();
            }
            self.current = node;
            node.addClass('active');
            node.getContent().display();
        };
        
        node.close = function () {
            node.removeClass('active');
            node.getContent().collapse();
        };
        
        return node;
    },
    
    createTabContent: function (index, content) {
        var node = document.createElement('DD');
        node.setHTML(content);
        node.addClass('tab');
        
        node.data = {
            index: index,
            content: content
        };
        
        node.getTitle= function () {
            return self.tabs[node.data.index].title;
        };
        
        return node;
    }
});

Mohawk.UI.Tabset.fromElement = function (element) {
    var tabs = [];
    
    var last_dt = 0;
    var last_dd = 0;
    
    for (var i = 0; i < element.childNodes.length; i ++) {
        if (element.childNodes[i].tagName) {
            switch (element.childNodes[i].tagName.toUpperCase()) {
            case 'DT':
                if (!tabs[last_dt]) {
                    tabs[last_dt] = {};
                }
                tabs[last_dt].title = element.childNodes[i].innerHTML;
                last_dt ++;
                break;
                
            case 'DD':
                if (!tabs[last_dd]) {
                    tabs[last_dd] = {};
                }
                tabs[last_dd].content = element.childNodes[i].innerHTML;
                last_dd ++;
                break;
            }
        }
    }
    
    var tabset = new Mohawk.UI.Tabset(tabs, element.attributes);
    return tabset;
};