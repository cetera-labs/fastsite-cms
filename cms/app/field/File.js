Ext.define('Cetera.field.File', {

    extend: 'Ext.form.TwinTriggerField',

    alias : 'widget.fileselectfield',

    onTrigger1Click: function() {
        this.window.show();
    },
    
    onTrigger2Click: function() {
        this.uploadWindow.show();
    },
    
    trigger1Cls:'icon-folder',
    trigger2Cls:'icon-upload',
    
    getUploadWindow : function() {
        this.uploadWindow = Ext.create('Cetera.window.Upload', { showPath: true });
        
        Ext.Ajax.request({
            url: '/cms/include/action_files.php',
            params: {action: 'upload_path'},
            scope: this,
            success: function(resp) {
                var obj = Ext.decode(resp.responseText);
                if (obj.path) this.uploadWindow.setPath(obj.path);
            }
        });
    },
    
    getWindow : function() {
        this.window = Ext.create('Cetera.fileselect.Window',{
            defaultExpand: this.defaultExpand || ''
        });
    },
    
    initComponent : function() {       
        this.getWindow();
        this.getUploadWindow();
      
        this.uploadWindow.on('successUpload', function(info) {
            var file = info.path + info.file;
            this.setValue(file);
            this.fireEvent('fileSelect', this, file);
        }, this);
        
        this.window.on('select', function(file) {
            this.setValue(file);
            this.fireEvent('fileSelect', this, file);
        }, this);
        
        this.callParent(arguments);
    },
        
  	onDestroy: function(){
        this.window.destroy();
        this.uploadWindow.destroy();
        this.callParent();
  	}
});
