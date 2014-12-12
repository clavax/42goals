include('utils.AnchorObserver');

Loader.includeLanguage('posts');
Loader.includeTemplate('comment-added');
Loader.includeTemplate('comment-form');

document.addLoader(function () {
    window.Comments = new (CommonFormProcessor.extend({
        template: COMMENT_ADDED,
        successHandler: function (req) {
            Progress.done(LNG.Done || 'Done', true);
    
            if (self.template) {
                Template.assign('data', req.data.item);
                var li = DOM.element('li', {
                    html: Template.transform(self.template),
                    id: 'comment-' + req.data.item.id
                });
                self.form.parentNode.replace(li);
            }
            self.form.reset();
            
            window.location.hash = 'comment-' + req.data.item.id;
        }
    }));
    
    Comments.Form = DOM.element('form', {
        id: 'comment-form',
        action: URL.home + 'api/comments/',
        method: Ajax.METHOD_POST,
        onsubmit: function () {
            Comments.submit(this);
            return false;
        },
        html: Template.transform(COMMENT_FORM)
    });
    
    var set_form = function (id) {
        if (!ENV.UID) {
            return;
        }
        var item = ID('comment-' + id) || ID('comments-list');
        if (!item) {
            return;
        }
        var list = item.firstTag('ul');
        if (!list) {
            list = DOM.element('ul', {
                appendTo: item
            });
        }
        if (!Comments.Form.parentNode || Comments.Form.parentNode.nodeType != DOM.ELEMENT_NODE) {
            Comments.Form.appendTo(DOM.element('li'));
        }
        list.appendChild(Comments.Form.parentNode);
        Comments.Form.setData({reply_to: id});
        Effects.scrollTo(Comments.Form);
    };
    set_form(0);
    
    AnchorObserver.add('reply-(\\d+)', set_form);
    AnchorObserver.start();
});