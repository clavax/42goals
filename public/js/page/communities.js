include('utils.common');
include('utils.site');
include('mohawk.utils.Date');
include('UI.Comments');

document.addLoader(function () {
    window.Community = new Singletone({
        join: function () {
            self.setMembership(true);
        },
        leave: function () {
            self.setMembership(false);
        },
        setMembership: function (value) {
            var req = new Ajax(URL.home + 'api/communities/' + ENV.community.id + '/' + (value ? 'join' : 'leave') + '/', Ajax.METHOD_POST);
            var container = ID('community-membership'); 
            container.addClass('loading');
            req.responseHandler = function (req) {
                var member = req.data.member;
                if (!member) {
                    Progress.done('Error');
                    container.removeClass('loading');
                    Console.log(req.data.error);
                } else {
                    container.removeClass('loading');
                    if (member == '1') {
                        container.addClass('joined');
                        container.removeClass('not-joined');
                    } else {
                        container.addClass('not-joined');
                        container.removeClass('joined');
                    }
                    Progress.done('Done', true);
                }
            };
            req.send();
        },
        
        assignAdmin: function (user, element) {
            self.setRole(true, user, element);
        },
        
        removeAdmin: function (user, element) {
            self.setRole(false, user, element);
        },
        
        setRole: function (value, user, element) {
            var req = new Ajax(URL.home + 'api/communities/' + ENV.community.id + '/admin/', value ? Ajax.METHOD_POST : Ajax.METHOD_DELETE);
            var container = element.ancestorTag('div');
            container.removeClass('loading', 'admin', 'member');
            container.addClass('loading');
            req.responseHandler = function (req) {
                var ok = req.data.ok;
                if (!ok) {
                    Progress.done('Error');
                    container.removeClass('loading');
                    Console.log(req.data.error);
                } else {
                    container.removeClass('loading', 'admin', 'member');
                    if (value) {
                        container.addClass('admin');
                    } else {
                        container.addClass('member');
                    }
                    Progress.done('Done', true);
                }
            };
            req.send({user: user});
        }
    });
});

document.addLoader(function () {
    if (document.body.id != 'communities-page') {
        return;
    }
    
    window.Community = new Singletone({
        join: function () {
        self.setMembership(true);
    },
    leave: function () {
        self.setMembership(false);
    },
    setMembership: function (value) {
        var req = new Ajax(URL.home + 'api/communities/' + ENV.community.id + '/' + (value ? 'join' : 'leave') + '/', Ajax.METHOD_POST);
        var container = ID('community-membership'); 
        container.addClass('loading');
        req.responseHandler = function (req) {
            var member = req.data.member;
            if (!member) {
                Progress.done('Error');
                container.removeClass('loading');
                Console.log(req.data.error);
            } else {
                container.removeClass('loading');
                if (member == '1') {
                    container.addClass('joined');
                    container.removeClass('not-joined');
                } else {
                    container.addClass('not-joined');
                    container.removeClass('joined');
                }
                Progress.done('Done', true);
            }
        };
        req.send();
    },
    
    makeAdmin: function (user, element) {
        self.setRole(true, user, element);
    },
    
    removeAdmin: function (user, element) {
        self.setRole(false, user, element);
    },
    
    setRole: function (value, user, element) {
        var req = new Ajax(URL.home + 'api/communities/' + ENV.community.id + '/admin/', value ? Ajax.METHOD_POST : Ajax.METHOD_DELETE);
        var container = element.ancestorTag('div');
        container.removeClass('loading', 'admin', 'member');
        container.addClass('loading');
        req.responseHandler = function (req) {
            var ok = req.data.ok;
            if (!ok) {
                Progress.done('Error');
                container.removeClass('loading');
                Console.log(req.data.error);
            } else {
                container.removeClass('loading', 'admin', 'member');
                if (value) {
                    container.addClass('admin');
                } else {
                    container.addClass('member');
                }
                Progress.done('Done', true);
            }
        };
        req.send({user: user});
    }
    });
});