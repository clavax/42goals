include('utils.common');
include('interface.PostsApi');
include('UI.PostsForm');
include('UI.PostsList');
include('UI.Shadow');

document.addLoader(function () {
    if (document.body.id != 'posts-admin') {
        return;
    }
    
    window.Posts = new PostsApi;
    
    Shadow.init();
    
    Posts.Form = new PostsForm;
    Posts.List = new PostsList('posts-list', Data.posts);
    ID('posts-list').replace(Posts.List.element);

    Observer.add('post-added', function (item) {
        Data.posts.push(item);
        Posts.List.addNode(item);
        Posts.Form.hide();
    });
    
    Observer.add('post-edited', function (item) {
        var ind = -1;
        foreach(Data.posts, function (i, post) {
            if (post.id == item.id) {
                ind = i;
                return false;
            }
        });
        if (ind >= 0) {
            Data.posts[ind] = item;
        } else {
            Data.posts.push(item);
        }
        var moved = ENV.category && item.category !== undefined && item.category != ENV.category;
        if (moved) {
            Posts.List.removeNode(Posts.List.getNode(item.id));
        } else {
            Posts.List.editNode(Posts.List.getNode(item.id), item);
        }
        Posts.Form.hide();
    });
    
    Observer.add('post-removed', function (item) {
        Posts.List.removeNode(Posts.List.getNode(item));
        Posts.Form.hide();
    });

    Observer.add('form-submitted', function (data) {
        if (!data.id) {
            Posts.add(data);
        } else {
            Posts.edit(data.id, data);
        }
    });  
});