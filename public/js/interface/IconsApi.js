include('mohawk.kernel.Ajax');

window.IconsApi = new Class({
    __construct: function () {
        self.Uploader = new SWFUpload ({
            upload_url : URL.home + 'api/upload/',
            flash_url : URL.js + '3dparty/SWFUpload/Flash/swfupload.swf',
            file_size_limit : '20 MB',
            
            button_text: '<span class="upload">Upload files</span>',
            button_text_style: '.upload {font-family: Arial; font-size: 12px; color: #3e4b99; text-decoration: underline;}',
            button_width: 70,
            button_height: 20,
            button_placeholder_id: 'icons-uploader',
            button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT,
            button_cursor : SWFUpload.CURSOR.HAND,
            
            file_queued_handler : function (file) {
                Icons.List.addNode({
                    id: file.id,
                    user: ENV.UID,
                    src: 'loading.png'
                });
            },
            file_queue_error_handler : function (file, errorCode, message) {
                var node = Icons.List.getNode(file.id);
                node.setStatus('error');
            },
            file_dialog_complete_handler : function (numFilesSelected, numFilesQueued) {
                this.startUpload();
            },
            upload_start_handler : function (file) {
                var node = Icons.List.getNode(file.id);
                node.setStatus('uploading...');
                
                return true;
            },
            upload_progress_handler : function (file, bytesLoaded, bytesTotal) {
                var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
                var node = Icons.List.getNode(file.id);
                node.setStatus(percent + '%');
            },
            upload_error_handler : function (file, errorCode, message) {
                var node = Icons.List.getNode(file.id);
                node.setStatus('error');
            },
            upload_success_handler : function (file, serverData) {
                var node = Icons.List.getNode(file.id);
                var data = XML.parse(serverData);
                if (!data || !data.file) {
                    node.setStatus('error');
                    return;
                }
                
                node.setStatus('uploaded');
                
                var req = new Ajax(URL.home + 'api/icons/', Ajax.METHOD_POST);
                req.responseHandler = function (req) {
                    var item = req.data.item;
                    if (!item) {
                        node.setStatus('error');
                        Console.log(req.data.error);
                    } else {
                        Icons.List.editNode(node, item);
                    }
                };
                req.send({tmp_name: data.file.tmp_name});
                
                /*
                var now = new Date(); // @todo: change to GMT time
                var file = {
                    id: 'new-' + (new Date()).valueOf(),
                    user: ENV.UID,
                    src: 'loading.png',
                    tmp_name: data.file.tmp_name
                };
                
                Effects.appear(node, function () {
                    Icons.List.editNode(node, file);
                });*/
                
                // insert file
            }
            // upload_complete_handler : self.uploadComplete,
            // queue_complete_handler : self.queueComplete  // Queue plugin event
        });    
    },
    
    edit: function () {
        // @todo
    },
    
    sort: function () {
        var data = [];
        var req = new Ajax(URL.home + 'api/icons/', Ajax.METHOD_PUT);
        for (var i = 0; i < Icons.List.element.childNodes.length; i ++) {
            var icon = Icons.List.element.childNodes[i];
            if (icon.clone === true) {
                continue;
            }
            data.push(icon.data.id);
        };
        req.send({id: data});
    },
    
    remove: function () {
        if (!self.List.selected.length) {
            return;
        }
        if (!confirm('Delete icon(s)? There is NO undo!')) {
            return;
        }

        var req = new Ajax(URL.home + 'api/icons/', Ajax.METHOD_DELETE);
        req.responseHandler = function (req) {
            var item = req.data.item;
            if (!item) {
                node.setStatus('error');
                Console.log(req.data.error);
            } else {
                if (!(item instanceof Array)) {
                    item = [item];
                }
                foreach(item, function (i, id) {
                    Icons.List.removeNode(Icons.List.getNode(id));                    
                });
            }
        };
        var data = [];
        foreach(self.List.selected, function (i, icon) {
            data.push(icon.data.id);
        });
        req.send({id: data});
    }
});