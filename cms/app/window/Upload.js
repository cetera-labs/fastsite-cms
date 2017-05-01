Ext.define('Cetera.window.Upload', {
    extend: 'Ext.Window',
	
	requires: 'Cetera.Ajax',
    
    layout:'fit',
    width:500,
    modal: true,
    title: Config.Lang.upload,
    closeAction:'hide',
    plain: true,
   
    initComponent : function(){
        if (this.showPath) {
            this.height = 150;
            this.pathField = new Ext.form.TextField({
                fieldLabel: Config.Lang.uploadCat,
                name: 'path',
                value: this.path,
                allowBlank: false      
            });
        } else {
            this.height = 120;
            this.pathField = new Ext.form.Hidden({
                name: 'path',
                value: this.path    
            });
        }
        
        this.fileField = new Ext.form.File({
                emptyText: Config.Lang.chooseFile,
                fieldLabel: Config.Lang.file,
                name: 'file',
                buttonText: '',
                allowBlank: false,
                buttonText: Config.Lang.chooseFile
        });
		
		this.progress = Ext.create('Ext.ProgressBar');	
    
        this.uploadForm = new Ext.FormPanel({
            fileUpload: true,
            waitMsgTarget: true,
            bodyStyle: 'background: none; padding: 10px 10px 0 10px;',
            fieldDefaults : {
                labelWidth: 130
            },

            border: false,
            defaults: {
                anchor: '0',
                allowBlank: false
            },
            items: [this.pathField,this.fileField, this.progress]
        });
        
        this.buttons = [{
            text: Config.Lang.doUpload,
            handler: this.uploadFile,
            scope: this
        },{
            text: Ext.MessageBox.buttonText.cancel,
            handler: function(){
                this.hide();
            },
            scope: this
        }];
        
        this.items = this.uploadForm;
        
        this.callParent(arguments);
    
    },
    
    uploadFile: function() {
        if(this.uploadForm.getForm().isValid()){
            if (this.pathField.getValue().substr(-1) != '/')
                this.pathField.setValue(this.pathField.getValue()+'/');
            if (this.pathField.getValue().substr(0,1) != '/')
                this.pathField.setValue('/'+this.pathField.getValue());
            
			var me = this;
			var formData = new FormData();
			var file = this.fileField.fileInputEl.dom.files[0];	
			
			formData.append('file', file);
			var prefix = _('Загрузка')+': '+file.name;
			
			Cetera.Ajax.request({
				url: 'include/action_files.php?action=upload&path='+this.pathField.getValue(),
				method: 'POST',
				rawData: formData,
				ignoreHeaders: true,
				success: function(resp) {
					var o = Ext.decode(resp.responseText);
					me.progress.updateProgress(0,'&nbsp;');
					me.hide();
					me.fireEvent('successUpload', {file: o.file, path: o.path});
					me.fileField.reset();
				},
				failure: function(resp) {
					var msg = _('Ошибка!');
                    var o = Ext.decode(resp.responseText);
                    if (o.message) msg += ' '+o.message;
					me.progress.updateProgress( 0, prefix + ' - ' + msg );					
				},
				uploadProgress: function(e) {
					me.progress.updateProgress( e.loaded/e.total, prefix + ' - ' + parseInt(e.loaded*100/e.total)+'%' );
				},
				scope: me
			});			
        }
    },
    
    setPath: function(path) {
        this.pathField.setValue(path);
    }
});