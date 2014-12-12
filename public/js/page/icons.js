include('interface.IconsApi');
include('js.3dparty.SWFUpload.swfupload');
include('js.3dparty.SWFUpload.plugins.queue');
include('UI.IconsAdminList');

document.addLoader(function () {
    if (document.body.id != 'page-icons') {
        return;
    }
    
    window.Icons = new IconsApi;
    
    Icons.List = new IconsAdminList('icons-list', Object.values(Data.icons));
    ID('icons-list').replace(Icons.List.element);
    Icons.List.refreshTargetObject();
    
    Observer.add(Icons.List.EVENT_SORTED, Icons.sort);
});