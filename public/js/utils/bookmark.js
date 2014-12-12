function bookmark(a) {
    var url = window.location.href;
    var title = document.title;
    if (window.external && window.external.addFavorite) {
        window.external.addFavorite(url, title);
    } else if (window.sidebar && window.sidebar.addPanel) {
        window.sidebar.addPanel(title, url, '');
    } else if (window.opera) {
        a.href = url;
        a.rel = "sidebar";
        a.title = url+','+title;
        return true;
    }
    return false;
}