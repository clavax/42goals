Loader.includeLanguage('me');
Loader.includeLanguage('site');
Loader.includeTemplate('owned-comparison');
Loader.includeTemplate('invited-comparison');

document.addLoader(function () {
    if (document.body.id != 'me-compares-page') {
        return;
    }

    var ComparesList = Mohawk.UI.List.extend({
        createNode: function (data) {
            var node = parent.createNode(data);
            node.purgeChildren();
            
            var is_owner = data.user == ENV.UID;
            var owner = null, current = null, others = [];
            foreach (data.items, function (i, item) {
                var item = Object.clone(item);
                if (item.user == ENV.UID) {
                    item.user = ENV.user;
                    item.user.id = ENV.UID;
                } else {
                    item.user = item.user in Data.friends ? Data.friends[item.user] : {id: item.user, name: '?'};
                }
                item.goal = item.goal_info || (item.goal in Data.goals ? Data.goals[item.goal] : {id: item.goal, title: '?'});
                
                if (item.user.id == data.user) {
                    owner = item;
                    if (is_owner) {
                        current == item;
                    }
                } else if (item.user.id == ENV.UID) {
                    current = item;
                } else {
                    others.push(item);
                }
            });
            
            Template.assign('comparison', {id: data.id, user: data.user, comment: data.comment});
            Template.assign('owner', owner);
            Template.assign('current', current);
            Template.assign('others', others);
            
            if (is_owner) {
                node.setHTML(Template.transform(OWNED_COMPARISON));
            } else {
                node.setHTML(Template.transform(INVITED_COMPARISON));
            }
            
            return node;
        }
    });
    
    if (ID('owned-compares')) {
        window.OwnedCompares = new ComparesList('owned-compares', Object.values(Data.owned_compares));
        OwnedCompares.element.addClass('compare-list');
        ID('owned-compares').replace(OwnedCompares.element);
    }
    
    if (ID('invited-compares')) {
        window.InvitedCompares = new ComparesList('invited-compares', Object.values(Data.invited_compares));
        InvitedCompares.element.addClass('compare-list');
        ID('invited-compares').replace(InvitedCompares.element);
    }
    
    window.Comparisons = new Singletone({
        submit: function (form) {
            var req = new Ajax(URL.home + 'api/comparisons/', Ajax.METHOD_POST);
            var data = form.getData();
            req.responseHandler = function (req) {
                var item = req.data.item;
                OwnedCompares.addNode(item);
                Progress.done(LNG.Done, true);
                form.reset();
            };
            req.send(data);
            Progress.load(LNG.Submitting);
        },
        
        remove: function (id) {
            if (!confirm(LNG.Confirm_delete)) {
                return;
            }
            var req = new Ajax(URL.home + 'api/comparisons/' + id + '/', Ajax.METHOD_DELETE);
            req.responseHandler = function (req) {
                var item = req.data.item;
                OwnedCompares.removeNode(OwnedCompares.getNode(item));
                Progress.done(LNG.Done, true);
            };
            req.send();
            Progress.load(LNG.Deleting);
        },
        
        accept: function (form) {
            var data = form.getData();
            var req = new Ajax(URL.home + 'api/comparisons/accept/' + data.id + '/', Ajax.METHOD_POST);
            req.responseHandler = function (req) {
                var item = req.data.item;
                var node = InvitedCompares.getNode(data.comparison);
                var node_data = node.data;
                foreach (node_data.items, function (i, item) {
                    if (item.user == ENV.UID) {
                        item.status = 'accepted';
                    }
                });
                InvitedCompares.editNode(node, node_data);
                Progress.done(LNG.Done, true);
            };
            req.send(data);
            Progress.load(LNG.Submitting);
        },
        
        reject: function (form) {
            var data = form.getData();
            var req = new Ajax(URL.home + 'api/comparisons/reject/' + data.id + '/', Ajax.METHOD_POST);
            req.responseHandler = function (req) {
                OwnedCompares.removeNode(InvitedCompares.getNode(data.comparison));
                Progress.done(LNG.Done, true);
            };
            req.send(data);
            Progress.load(LNG.Submitting);
        }
    });
});