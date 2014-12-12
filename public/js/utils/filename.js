window.filename = {
	ext: {
		'image/gif': 'gif',
		'image/jpeg': ['jpg', 'jpeg'],
		'image/png': 'png'
	},

    get_ext: function (file) {
        var i = file.lastIndexOf('.');
        return file.substr(i + 1).toLowerCase();
    },

    get_name: function (file) {
        var dot = file.lastIndexOf('.');
        var slash = file.lastIndexOf('/');
        var name = file.substr(slash + 1, dot - slash - 1);
        var re = new RegExp('^(.+)\\(\\d+\\)$');
        var m = null;
        if (m = name.match(re)) {
            name = m[1];
        }
        return name;
    },
    
    mime2ext: function (mimetype) {
    	if (filename.ext[mimetype]) {
    		var exts = filename.ext[mimetype];
    		return exts instanceof Array ? exts : [exts];
    	}
    }
}