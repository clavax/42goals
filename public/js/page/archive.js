include('utils.common');
include('interface.GoalsApi');
include('UI.ArchiveList');

Loader.includeLanguage('site');
Loader.includeLanguage('goals');

document.addLoader(function () {
    if (document.body.id != 'archive-page') {
        return;
    }
    
    window.Goals = new GoalsApi;
    Goals.Archive = new ArchiveList('archive-list', []);
    ID('archive-list').replace(Goals.Archive.element);
    
    Observer.add('goal-removed', function (id) {
    	var node = Goals.Archive.getNode(id);
    	Goals.Archive.removeNode(node);
    });
    
    Observer.add('goal-edited', function (item) {
    	var node = Goals.Archive.getNode(item.id);
    	Goals.Archive.removeNode(node);
    });
    
    Observer.add(Goals.Archive.EVENT_EMPTY, function () {
    	ID('archive-empty').removeClass('hidden');
    });
    
    Observer.add(Goals.Archive.EVENT_NOTEMPTY, function () {
    	ID('archive-empty').addClass('hidden');
    });
    
    Goals.Archive.setChildren(Data.goals);
});