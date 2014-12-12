include('utils.common');
include('interface.TemplatesApi');
include('interface.CategoriesApi');
include('UI.TemplatesForm');
include('UI.CategoriesForm');
include('UI.TemplatesList');
include('UI.TemplatesCategories');
include('UI.Shadow');
include('utils.AnchorObserver');

document.addLoader(function () {
    if (document.body.id != 'templates-admin') {
        return;
    }
    
    window.Templates = new TemplatesApi;
    
    Shadow.init();
    
    Templates.Form = new TemplatesForm;
    Templates.List = new TemplatesList('templates-list', Data.templates);
    ID('templates-list').replace(Templates.List.element);

    Templates.Categories = new TemplatesCategories('templates-categories', Data.categories);
    document._toHideOnClick.push(Templates.Categories);
    ID('templates-categories').replace(Templates.Categories.element);
    if (Templates.Categories.getNode(ENV.tab)) {
    	Templates.Categories.select(Templates.Categories.getNode(ENV.category));
    }
    
    window.Categories = new CategoriesApi;
    Categories.Form = new CategoriesForm;
    
    Observer.add('category-added', function (item) {
    	Data.categories.push(item);
    	Categories.Form.hide();
    	Templates.Categories.addNode(item);
    });
    
    Observer.add('category-edited', function (item) {
        var id = -1;
        foreach(Data.categories, function (i, category) {
            if (category.id == item.id) {
                id = i;
                return false;
            }
        });
        if (id > 0) {
            Data.categories[id] = item;
        } else {
            Data.categories.push(item);
        }
        Categories.Form.hide();
        Templates.Categories.editNode(Templates.Categories.getNode(item.id), item);
    });
    
    Observer.add('category-removed', function (id) {
    	Templates.Categories.removeNode(Templates.Categories.getNode(id));
    });
    
    Observer.add(Templates.Categories.EVENT_SORTED, function () {
    	Categories.sort();
    });
    
    AnchorObserver.add('(?:(?:category-(\\d+))|(all-templates))?', function (id, all_templates) {
    	var category = null;
		if (all_templates !== undefined) {
			ENV.category = 0;
		} else if (id !== undefined) {
    		ENV.category = id;
    		category = Templates.Categories.getNode(id);
    		if (!category) {
    			// no categories
    			return;
    		}
    	} else {
    		tab = Templates.Categories.element.firstChild;
    		if (tab) {
    			ENV.tab = tab.data.id;
    		} else {
    			ENV.tab = 0;
    		}
    	}
		Progress.load(LNG.Loading_tab);
		if (category) {
			Templates.Categories.select(category);
			ID('all-templates').removeClass('selected');
		} else {
			Templates.Categories.unselect();
			ID('all-templates').addClass('selected');
		}
    	Templates.List.element.removeChildren();
	    foreach(Data.templates, function (i, template) {
	    	if (!ENV.category|| template.category== ENV.category) {
	    		Templates.List.addNode(template);
	    	}
	    });
	    Progress.done(LNG.Done, true);
    });
    AnchorObserver.start();
    
    Observer.add('template-added', function (item) {
    	Data.templates.push(item);
    	Templates.List.addNode(item);
    	Templates.Form.hide();
    });
    
    Observer.add('template-edited', function (item) {
        var ind = -1;
        foreach(Data.templates, function (i, template) {
            if (template.id == item.id) {
                ind = i;
                return false;
            }
        });
        if (ind >= 0) {
            Data.templates[ind] = item;
        } else {
            Data.templates.push(item);
        }
        var moved = ENV.category && item.category !== undefined && item.category != ENV.category;
        if (moved) {
        	Templates.List.removeNode(Templates.List.getNode(item.id));
        } else {
        	Templates.List.editNode(Templates.List.getNode(item.id), item);
        }
        Templates.Form.hide();
    });

    Observer.add('template-sorted', function (item) {
    	foreach(Data.templates, function (i, template) {
    		foreach(item, function (j, data) {
        		if (goal.id == data.id) {
        			Data.templates[i].position = data.position;
        			return false;
        		}
    		});
    	});
    });
    
    Observer.add('template-removed', function (item) {
    	Templates.List.removeNode(Templates.List.getNode(item));
    	Templates.Form.hide();
    });

    Observer.add('form-submitted', function (data) {
        if (!data.id) {
            data.position = Templates.List.element.childNodes.length;
            Templates.add(data);
        } else {
        	Templates.edit(data.id, data);
        }
    });  
    
    Observer.add('categories-submitted', function (data) {
    	if (!data.id) {
    		data.position = Templates.Categories.element.childNodes.length;
    		Categories.add(data);
    	} else {
    		Categories.edit(data.id, data);
    	}
    });  
    
    Observer.add(Templates.List.EVENT_SORTED, function () {
    	Templates.sort();
    });
});