include('UI.Uploader');

window.CommunitiesForm = new Class ({
    element: null,
    
    __construct: function () {
        self.element = self.createForm();
    
        document.body.appendChild(self.element);
        self.element.addClass('hidden');
    },
    
    createForm: function () {
        var form = document.createElement('FORM');
        form.object = self;
        
        Loader.includeTemplate('communities-form');
        form.setHTML(Template.transform(COMMUNITIES_FORM));
        form.id = 'goals-form';
        
        form.onmousedown = DOM.stopEvent;
        form.onsubmit = function () {
            self.save.apply(self, [form]);
            return false;
        };
        
        form.PictureUploader = new Uploader(form.getElementById('community-picture-uploader'));
        form.PictureUploader.onUpload = function (data) {
            if (!data || !data.file) {
                Progress.done('Error');
                return;
            }
            
            form.setData({picture_tmpname: data.file.tmp_name, picture_name: data.file.name, picture_size: data.file.size});
        };
        
        form.ThumbnailUploader = new Uploader(form.getElementById('community-thumbnail-uploader'));
        form.ThumbnailUploader.onUpload = function (data) {
            if (!data || !data.file) {
                Progress.done('Error');
                return;
            }
            
            form.setData({thumbnail_tmpname: data.file.tmp_name, thumbnail_name: data.file.name, thumbnail_size: data.file.size});
        };
        
        return form;
    },
    
    save: function (form) {
        foreach(form.getElementsByClassName('error'), 
            function () {
                this.remove();
            }
        );
        
        var data = form.getData();
        Observer.fire('form-submitted', data);
    },
    
    set: function (data) {
        self.element.removeClass('hidden');
        Shadow.show();
        Dragdrop.bringToFront(self.element);
        self.element.setData({'id': ''});
        self.element.reset();
        if (data) {
            self.element.replaceClass('adding', 'editing');
            self.element.setData(data);
        } else {
            self.element.replaceClass('editing', 'adding');
        }
    },
    
    hide: function () {
        Shadow.hide();
        self.element.addClass('hidden');
    }
});