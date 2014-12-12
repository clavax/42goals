include('UI.Carousel');
include('mohawk.UI.SelectableList');

Loader.includeTemplate('popup-image');

document.addLoader(function () {
    if (document.body.id != 'home-page') {
        return;
    }
    
    window.Popup = new Singletone({
        __construct: function () {
            self.container = DOM.element('div', {
                id: 'popup-image-container',
                html: Template.transform(POPUP_IMAGE)
            });
            self.img_container = self.container.getElementById('popup-image');
            
            foreach(ID('features').getElementsByClassName('screenshot'), function () {
                this.onclick = function () {
                    self.open(this);
                };
            });
            
            self.loading = DOM.element('img', {
                src: URL.img + 'site/loading24.gif',
                className: 'loading'
            });
        },
        
        open: function (img) {
            
            self.img_container.setChild(DOM.element('img', {
                src: img.src.replace(/\.png/, '-big.png'),
                onload: function () {
                    self.loading.remove();
                    Shadow.show();
                    self.container.appendTo(ID('wrapper'));
                    Dragdrop.bringToFront(self.container);
                    Effects.appear(this);
                },
                onerror: function () {
                    self.loading.remove();
                }
            }));
            self.loading.appendTo(img.parentNode);
        },
        
        close: function () {
            self.container.remove();
            Shadow.hide();
        }
    });
    
    Shadow.init();
});