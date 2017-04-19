Ext.define('Cetera.window.Upload', {
    extend: 'Ext.Window',
    
    layout:'fit',
    width:500,
    modal: true,
    title: Config.Lang.upload,
    closeAction:'hide',
    plain: true,
   
    initComponent : function(){
        if (this.showPath) {
            this.height = 140;
            this.pathField = new Ext.form.TextField({
                fieldLabel: Config.Lang.uploadCat,
                name: 'path',
                value: this.path,
                allowBlank: false      
            });
        } else {
            this.height = 110;
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
            items: [this.pathField,this.fileField]
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
            var cmp = this;
            this.uploadForm.getForm().submit({
              url: '/cms/include/action_files.php',
              params: {
                  action: 'upload',
                  showPath: this.showPath
              },
              waitMsg: Config.Lang.wait,
              success: function(fp, o){
                  cmp.hide();
                  cmp.fireEvent('successUpload', {file: o.result.file, path: cmp.pathField.getValue()});
                  cmp.fileField.reset();
              },
              failure: function(fp, o){
                  var obj = Ext.decode(o.response.responseText);
                  if (obj.message) Ext.MessageBox.alert(Config.Lang.error, obj.message);
              }
          });
        }
    },
    
    setPath: function(path) {
        this.pathField.setValue(path);
    }
});