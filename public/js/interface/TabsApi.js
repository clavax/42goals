include('mohawk.kernel.Ajax');

window.TabsApi = new Class({
    add: function (data) {
        var req = new Ajax(URL.home + 'api/tabs/', Ajax.METHOD_POST);
        req.responseHandler = function (req) {
            if (req.data.item) {
                Observer.fire('tab-added', req.data.item);
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
        var req = new Ajax(URL.home + 'api/tabs/' + id + '/', Ajax.METHOD_PUT);
        req.responseHandler = function (req) {
            if (req.data.item) {
                Observer.fire('tab-edited', req.data.item);
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
        var req = new Ajax(URL.home + 'api/tabs/' + id + '/', Ajax.METHOD_DELETE);
        req.responseHandler = function (req) {
            if (req.data.item) {
                var item = req.data.item;
                Observer.fire('tab-removed', item);
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
    	var data = {};
    	data.title = prompt(LNG.Input_new_tab_name);
    	data.position = parseInt(Goals.Tabs.element.lastChild ? Goals.Tabs.element.lastChild.data.position : 0) + 1;
    	if (data.title === null || !data.title.length) {
    		return;
    	}
    	self.add(data);
    },
    
    sort: function () {
        // get order
        var pos = 0;
        var data = [];
        var nodes = Goals.Tabs.element.childNodes;
        for (var i = 0; i < nodes.length; i ++) {
        	if (nodes[i].cloned) {
        		continue;
        	}
            pos ++;
            data.push({id: nodes[i].data.id, position: pos});
        }
        
        // send request
        var req = new Ajax(URL.home + 'api/tabs/', Ajax.METHOD_PUT);
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