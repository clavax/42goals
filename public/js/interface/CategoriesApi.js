include('mohawk.kernel.Ajax');

window.CategoriesApi = new Class({
    add: function (data) {
        var req = new Ajax(URL.home + 'api/categories/', Ajax.METHOD_POST);
        req.responseHandler = function (req) {
            if (req.data.item) {
                Observer.fire('category-added', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                Progress.done('Error');
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send({data: data});
        Progress.load(LNG.Saving);
    },
    
    edit: function (id, data) {
        var req = new Ajax(URL.home + 'api/categories/' + id + '/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
            if (req.data.item) {
                Observer.fire('category-edited', req.data.item);
                Progress.done(LNG.Saved, true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send({data: data});
        Progress.load(LNG.Saving);
    },
    
    remove: function (id) {
        if (!confirm('Delete? There is NO undo')) {
            return;
        }
        var req = new Ajax(URL.home + 'category/categories/' + id + '/', Ajax.METHOD_DELETE);
        req.responseHandler = function (req) {
            if (req.data.item) {
                var item = req.data.item;
                Observer.fire('category-removed', item);
                Progress.done('Deleted', true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send();
        Progress.load('Deleting');
    },
    
    create: function () {
    	Categories.Form.set();
    },
    
    sort: function () {
        // get order
        var pos = 0;
        var data = [];
        var nodes = Templates.Categories.element.childNodes;
        for (var i = 0; i < nodes.length; i ++) {
        	if (nodes[i].cloned) {
        		continue;
        	}
            pos ++;
            data.push({id: nodes[i].data.id, position: pos});
        }
        
        // send request
        var req = new Ajax(URL.home + 'api/categories/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
            if (req.data.ok) {
                Progress.done(LNG.Saved, true);
            } else {
                Progress.done(LNG.Error);
            }
        };
        req.errorHandler = function (req) {
            Progress.done(LNG.Error);
        };
        req.send({data: data});
        Progress.load(LNG.Saving);
    }
});