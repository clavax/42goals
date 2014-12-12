include('utils.common');
include('interface.CommunitiesApi');
include('UI.CommunitiesForm');
include('UI.CommunitiesList');
include('UI.Shadow');

document.addLoader(function () {
    if (document.body.id != 'communities-admin') {
        return;
    }
    
    window.Communities = new CommunitiesApi;
    
    Shadow.init();
    
    Communities.Form = new CommunitiesForm;
    Communities.List = new CommunitiesList('communities-list', Data.communities);
    ID('communities-list').replace(Communities.List.element);

    Observer.add('community-added', function (item) {
        Data.communities.push(item);
        Communities.List.addNode(item);
        Communities.Form.hide();
    });
    
    Observer.add('community-edited', function (item) {
        var ind = -1;
        foreach(Data.communities, function (i, community) {
            if (community.id == item.id) {
                ind = i;
                return false;
            }
        });
        if (ind >= 0) {
            Data.communities[ind] = item;
        } else {
            Data.communities.push(item);
        }
        Communities.List.editNode(Communities.List.getNode(item.id), item);
        Communities.Form.hide();
    });

    Observer.add('community-removed', function (item) {
        Communities.List.removeNode(Communities.List.getNode(item));
        Communities.Form.hide();
    });

    Observer.add('form-submitted', function (data) {
        if (!data.id) {
            data.position = Communities.List.element.childNodes.length;
            Communities.add(data);
        } else {
            Communities.edit(data.id, data);
        }
    });  
});