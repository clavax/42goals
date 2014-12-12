include('utils.common');
include('utils.site');
include('mohawk.utils.Date');
include('interface.GoalsApi');
include('interface.ChartsApi');
include('UI.ChartConstructor');
include('UI.MeLayout');
include('UI.MeGoals');
include('UI.Wysiwyg');
include('UI.Uploader');

Loader.includeTemplate('post-added');
Loader.includeTemplate('post-edited');
Loader.includeTemplate('post-removed');

document.addLoader(function () {
    window.Me = {};
    if (ID('profile-form')) {
        Me.Profile = {
            init: function () {
                var form = ID('profile-form');
                
                var inputs = [form.getElementsByTagName('input')];
                inputs[1] = form.getElementsByTagName('textarea');
    
                for (var k = 0; k < inputs.length; k ++) {
                    for (var i = 0; i < inputs[k].length; i ++) {
                        var input = inputs[k][i];
                        input.onfocus = function () {
                            if (this.value == this.title) {
                                this.value = ''; 
                                this.removeClass('empty');
                            }
                        };
                        input.onblur = function () {
                            if (this.value == '') {
                                this.value = this.title; 
                                this.addClass('empty');
                            }
                        };
                        input.onchange = function () {
                            Me.Profile.save(this);
                        };
                        if (input.value == '') {
                            input.value = input.title;
                            input.addClass('empty');
                        }
                    }
                }
            },
            save: function (element) {
                var req = new Ajax(URL.home + 'api/settings/info/', Ajax.METHOD_POST);
                req.responseHandler = function (req) {
                    var item = req.data.item;
                    if (!item) {
                        Progress.done('Error');
                        Console.log(req.data.error);
                    } else {
                        Progress.done('Done', true);
                    }
                };
                var data = {};
                data[element.name] = element.value;
                req.send({data: data});
            },
            
            setPublic: function (value) {
                var req = new Ajax(URL.home + 'api/settings/info/', Ajax.METHOD_POST);
                var container = ID('profile-publicity'); 
                container.addClass('loading');
                req.responseHandler = function (req) {
                    var item = req.data.item;
                    if (!item) {
                        Progress.done('Error');
                        container.removeClass('loading');
                        Console.log(req.data.error);
                    } else {
                        container.removeClass('loading');
                        if (item['public'] == '1') {
                            container.addClass('public');
                            container.removeClass('private');
                        } else {
                            container.addClass('private');
                            container.removeClass('public');
                        }
                        Progress.done('Done', true);
                    }
                };
                var data = {'public': value};
                req.send({data: data});
            }
        };
        Me.Profile.init();
    }
    
    if (ID('profile-picture-uploader')) {
        Me.Uploader = new Uploader(ID('profile-picture-uploader'));
        Me.Uploader.onUpload = function (data) {
            if (!data || !data.file) {
                Progress.done('Error');
                return;
            }
            
            var req = new Ajax(URL.home + 'api/settings/picture/', Ajax.METHOD_POST);
            req.responseHandler = function (req) {
                var item = req.data.item;
                if (!item) {
                    Progress.done('Error: ' + req.data.error);
                } else {
                    var container = ID('profile-picture');
                    container.removeClass('empty');
                    var img = DOM.element('img', {src: URL.userpics + item.picture});
                    if (container.firstTag('img')) {
                        container.replaceChild(img, container.firstTag('img'));
                    } else {
                        container.insertFirst(img);
                    }
                    Progress.done('Done', true);
                }
            };
            req.send({tmp_name: data.file.tmp_name, real_name: data.file.name});
        };
    }
    
    //if (ID('connection-status')) {
        window.Connection = new Singletone({
            request: function (id, element) {
                self.setStatus(true, id, element);
            },
            
            remove: function (id, element) {
                self.setStatus(false, id, element);
            },
            
            setStatus: function (value, id, element) {
                var req = new Ajax(URL.home + 'api/connections/' + id + '/', value ? Ajax.METHOD_POST : Ajax.METHOD_DELETE);
                var container = element ? element.ancestorTag('div') : ID('connection-status');
                container.removeClass('loading', 'none', 'requested', 'accepted');
                container.addClass('loading');
                req.responseHandler = function (req) {
                    if (req.data.status === undefined) {
                        Progress.done('Error');
                        container.removeClass('loading');
                        Console.log(req.data.error);
                    } else {
                        container.removeClass('loading', 'none', 'requested', 'accepted');
                        if (req.data.status == '1') {
                            container.addClass('requested');
                        } else {
                            container.addClass('none');
                        }
                        Progress.done('Done', true);
                    }
                };
                req.send();
            }
        });
    //}
        
    window.Notifications = new Singletone({
        read: function(element) {
            var m = element.id.match(/notification-(\d+)/);
            var id = m[1];
            var req = new Ajax(URL.home + 'api/notifications/' + id + '/', Ajax.METHOD_POST);
            req.responseHandler = function () {
                window.location = element.href;
                Progress.done(LNG.Done, true);
            };
            req.send();
            Progress.load(LNG.Loading);
            return false;
        },
        
        readAll: function() {
            var req = new Ajax(URL.home + 'api/notifications/readall/', Ajax.METHOD_POST);
            req.responseHandler = function (req) {
                if (req.data.ok) {
                    var container = ID('notifications');
                    if (container) {
                        ID('notifications').remove(Effects.vanish);
                    }
                }
                Progress.done(LNG.Done, true);
            };
            req.send();
            Progress.load(LNG.Loading);
            return false;
        }
    });

    if (IE && !IE8) {
        var menu = ID('userbox-menu');
        if (menu) {
            var name = ID('userbox-name');
            menu.appendTo(document.body);
            menu.alignTo(name, 'left', true);
            menu.adjoinTo(name, 'bottom', true);
            menu.style.width = name.offsetWidth + 32 + 'px';
            menu.style.marginTop = '1px';
            menu.style.marginLeft = '-1px';
            
            name.onmouseover = function () {
                menu.style.display = 'block';
            };
            name.onmouseout = function () {
                menu.style.display = 'none';
            };
            menu.onmouseover = function () {
                menu.style.display = 'block';
                name.parentNode.addClass('over');
            };
            menu.onmouseout = function () {
                menu.style.display = 'none';
                name.parentNode.removeClass('over');
            };
        }
        
        var list = ID('notifications-list');
        if (list) {
            var indicator = ID('notifications-indicator');
            list.appendTo(document.body);
            list.adjoinTo(indicator, 'bottom', true);
            list.style.marginTop = '1px';
            list.style.marginLeft = '1px';
            
            indicator.onmouseover = function () {
                list.style.display = 'block';
                list.alignTo(indicator, 'right', true);
            };
            indicator.onmouseout = function () {
                list.style.display = 'none';
            };
            list.onmouseover = function () {
                list.style.display = 'block';
                indicator.parentNode.addClass('over');
            };
            list.onmouseout = function () {
                list.style.display = 'none';
                indicator.parentNode.removeClass('over');
            };
        }
    }
    
    function profile_url() {
        var container = ID('profile-url');
        if (!container || container.tagName != 'SPAN') {
            return;
        }
        var url = container.title;
        if (!url.match(/http:\/\//)) {
            url = 'http://' + url;
        }
        container.replace(DOM.element('a', {
            href: url,
            html: container.innerHTML
        }));
    }

    profile_url();
});

document.addLoader(function () {
    if (document.body.id != 'me-index-page') {
        return;
    }
    
    window.Charts = new ChartsApi;
    window.Goals = new GoalsApi;

    Shadow.init();
    
    Me.Constructor = new ChartConstructor;

    Data.layout = [
        {
            id: 1
        },
        {
            id: 2
        },
        {
            id: 3,
            size: 'wide'
        }
    ];
    
    Data.layout_chart = {};
    Data.chart_layout = {};
    
    foreach(Data.layout, function (i, layout) {
        foreach(Data.charts, function (j, chart) {
            if (layout.id == chart.position) {
                chart.accumulate  *= 1;
                chart.interpolate *= 1;
                chart.fill_empty  *= 1;
                Data.layout[i].chart = chart;
                Data.layout_chart[layout.id] = chart.id;
                Data.chart_layout[chart.id] = layout.id;
            }
        });
    });
    
    Me.Layout = new MeLayout('me-layout', Data.layout);
    ID('me-layout').replace(Me.Layout.element);
    
    Me.Chart = ChartFactory.factory(ENV.chart_type);
    
    Observer.add('chart-added', function (item) {
        Data.chart_layout[item.id] = item.position;
        Data.layout_chart[item.position] = item.id;
        var node = Me.Layout.getNode(item.position);
        item.accumulate  *= 1;
        item.interpolate *= 1;
        item.fill_empty  *= 1;
        node.data.chart = item;
        node.removeClass('empty');
        draw_node_chart(node);
    });
    
    Observer.add('chart-edited', function (item) {
        var node = Me.Layout.getNode(item.position);
        item.accumulate  *= 1;
        item.interpolate *= 1;
        item.fill_empty  *= 1;
        node.data.chart = item;
        node.header.setHTML(item.title || '');
        node.removeClass('empty');
        draw_node_chart(node);
    });
    
    Observer.add('chart-removed', function (id) {
        var layout = Data.chart_layout[id];
        var node = Me.Layout.getNode(layout);
        node.clear();
        delete(Data.chart_layout[id]);
        delete(Data.layout_chart[layout]);
    });
    
    function draw_node_chart(node) {
        var chart = node.data.chart;
        if (chart) {
            ChartData.setGoalId(chart.goal);
            ChartData.setGroup(chart.groupby);
            ChartData.setPeriod(chart.period);
            
            var data;
            if (chart.type == 'pie') {
                data = ChartData.getBooleanData();
            } else {
                data = chart.accumulate ? ChartData.getAccumulatedData() : ChartData.getGroupedData();
            }
            var w = node.data.size == 'wide' ? 662 : 320;
            var h = 240;

            Me.Chart.set({
                canvas: node.canvas.id,
                width: w,
                height: h,
                type: chart.type,
                interpolate: chart.interpolate,
                data: data
            });
            Me.Chart.draw();
        }
    }
    
    foreach(Me.Layout.element.childNodes, function (i, node) {
        draw_node_chart(node);
    });
});

document.addLoader(function () {
    if (document.body.id != 'me-goals-page') {
        return;
    }
    
    window.Goals = new GoalsApi;

    Me.Goals = new MeGoals('me-goals-list', Data.goals);
    ID('me-goals-list').replace(Me.Goals.element);
    
    Observer.add('goal-edited', function (item) {
        var node = Me.Goals.getNode(item.id);
        node.replaceClass('loading', item.privacy);
    });
});

document.addLoader(function () {
    if (document.body.id != 'me-posts-add') {
        return;
    }
    
    window.Posts = new (CommonFormProcessor.extend ({
        template: POST_ADDED,

        Wg: Wysiwyg.replace(ID('post-text')),
        
        Form: ID('me-post-form'),
        
        submit: function (form) {
            self.Wg.setText();
            parent.submit(form);
        },
        
        fetch: function (form) {
            var req = new Ajax(URL.home + 'api/fetch-link/');
            req.responseHandler = function (req) {
                var item = req.data.item;
                if (!item) {
                    item = {
                        title: '',
                        body: ''
                    };
                }
                self.Form.setData({
                    title: item.title,
                    text: item.body 
                }, 'data');
                self.Wg.setHTML();

                ID('post-fetch').addClass('hidden');
                ID('post-title').ancestorTag('li').removeClass('hidden');
                ID('post-text').ancestorTag('li').removeClass('hidden');
                ID('post-save').removeClass('hidden');
                
                Progress.done(LNG.Done, true);
            };
            var data = form.getData();
            req.send({url: data['data[url]']});
            Progress.load(LNG.Submitting);
        },
        
        setType: function (type) {
            switch (type) {
            case 'link':
                ID('post-url').ancestorTag('li').removeClass('hidden');
                ID('post-fetch').removeClass('hidden');
                
                ID('post-title').ancestorTag('li').addClass('hidden');
                ID('post-text').ancestorTag('li').addClass('hidden');
                ID('post-save').addClass('hidden');
                break;
                
            default:
            case 'post':
                ID('post-url').ancestorTag('li').addClass('hidden');
                ID('post-fetch').addClass('hidden');
                
                ID('post-title').ancestorTag('li').removeClass('hidden');
                ID('post-text').ancestorTag('li').removeClass('hidden');
                ID('post-save').removeClass('hidden');
                break;
            }
        }
    }));
    
});

document.addLoader(function () {
    if (document.body.id != 'me-posts-edit') {
        return;
    }
    
    window.Wg = Wysiwyg.replace(ID('post-text'));
    window.Posts = new (CommonFormProcessor.extend ({
        template: POST_EDITED,
        method: Ajax.METHOD_PUT,
        submit: function (form) {
            Wg.setText();
            parent.submit(form);
        }
    }));
    
    window.PostsRemove = new (CommonFormProcessor.extend ({
        template: POST_REMOVED,
        method: Ajax.METHOD_DELETE,
        submit: function (form) {
            if (!confirm('Delete?')) {
                return false;
            }
            parent.submit(form);
            return false;
        }
    }));
});