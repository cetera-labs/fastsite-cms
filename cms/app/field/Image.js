Ext.define('Cetera.field.Image', {

    extend:'Cetera.field.Panel',
	
	alias : 'widget.imagefield',
    
    backupValue: false,
    
    validate : function() {
      
        this.panel.removeBodyCls('error');
        if (this.allowBlank || this.getValue()) return true;
        this.panel.addBodyCls('error');
        return false;
 
    },
    
    setValue : function(value) {
    
        this.backupValue = this.getValue();
    
        this.info.update('');
    
        if (value) {
            this.preview.update('<img src="/cms/include/image.php?dontenlarge=1&width=300&height='+this.height+'&src='+value+'">');
            this.btnDelete.show();
            this.btnUnDelete.hide();
            
			this.info.update(value);
            Ext.Ajax.request({
                url: '/cms/include/data_image_info.php',
                params: {
                    path: value
                },
                scope: this,
                success: function(resp) {
                    var obj = Ext.decode(resp.responseText);
                    if (obj.data) this.info.update(value + '<br>' + obj.data[0] + 'x' + obj.data[1] + 'px<br>' + obj.size);
                }
            });            
            
        } else {
            if (this.backupValue) {
                this.preview.update('<img src="/cms/include/image.php?dontenlarge=1&width=300&height='+this.height+'&src='+this.backupValue+'"><div style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.8); line-height: '+this.height+'px">'+Config.Lang.picToBeDeleted+'</div>');
                this.btnUnDelete.show();
            } else {
                this.preview.update('');
            }
            this.btnDelete.hide();
        }
        this.callParent(arguments);
    
    },
    
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
        
        this.uploadWindow.on('successUpload', function(info) {
            var file = info.path + info.file;
            this.setValue(file);
        }, this);        
    },
    
    getWindow : function() {
        this.window = Ext.create('Cetera.fileselect.Window', {
            extension: 'gif,jpg,jpeg,png,svg',
            activePanel: 1
        });
        
        this.window.on('select', function(file) {
            this.setValue(file);
        }, this);        
    },    
    
    getPanel : function() {

        this.getWindow();
        this.getUploadWindow();    
    
        this.preview = Ext.create('Ext.Panel',{
            layout  : 'border',
            border  : false,
            bodyCls :'chess',
            bodyStyle : 'text-align: center; display: table-cell; vertical-align: middle',
            width   : '60%',
            region  : 'west'
        });
        
        this.info = Ext.create('Ext.Panel',{
			width   : '100%',
            layout  : 'border',
            border  : false,
            bodyStyle : '',
            html    : 'info',
            padding : '10 0 0 0', 
            flex    : 1
        });        
        
        this.btnSelect = Ext.create('Ext.Button',{
            text  : Config.Lang.selectFile,
            iconCls: 'icon-folder',
            scope   : this,
            width   : '100%',
            handler : function() {
                this.window.show();
            }            
        });
        this.btnUpload = Ext.create('Ext.Button',{
            text  : Config.Lang.upload2,
            iconCls: 'icon-upload',
            scope   : this,
            width   : '100%',
            handler : function() {
                this.uploadWindow.show();
            }            
        });
        this.btnDelete = Ext.create('Ext.Button',{
            text    : Config.Lang.remove,
            iconCls : 'icon-delete',
            hidden  : true,
            scope   : this,
            width   : '100%',
            handler : function() {
                this.setValue('');
            }
        });
        this.btnUnDelete = Ext.create('Ext.Button',{
            text  : Config.Lang.unDelete,
            hidden: true,
            scope   : this,
            width   : 200,
            handler : function() {
                this.setValue(this.backupValue);
            }            
        });
    
        return new Ext.Panel({
            layout    : 'border',
            height    : this.height,
			width     : 600,
            border    : false,
            bodyStyle : 'background: none',
            items: [
                this.preview,
                {
                    region    : 'center',
                    border    : false,
                    bodyStyle :'background: none; padding-left: 5px',                    
                    layout: {
                        type: 'vbox',
                        align: 'left',
                        defaultMargins: {top: 0, right: 0, bottom: 2, left: 0}
                    },
                    items     : [
                        this.btnSelect, 
                        this.btnUpload, 
                        this.btnDelete, 
                        this.btnUnDelete,
                        this.info
                    ]
                }
            ]
        });    
    
    },
    
  	onDestroy: function(){
        this.window.destroy();
        this.uploadWindow.destroy();
        this.callParent(arguments);
  	}
	
});