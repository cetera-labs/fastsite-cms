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
	
	getRealValue(value) {
		var a = value.split('/');
		a.shift();
		if (a.shift() == 'imagetransform') {
			a.shift();
			value = '/' + a.join('/');
		}
		return value;
	},
	
	getPreviewSrc: function(value) {
		return '/cms/include/image.php?dontenlarge=1&width=1000&height='+this.height+'&src='+this.getRealValue(value)+'&r='+( new Date() * 1 );
	},
    
    setValue : function(value, keepBackup, saveBackup) {
    
		if (!this.backupValue) this.backupValue = this.getValue();
	
        this.info.update('');
    
        if (value) {
			
			if (!keepBackup) this.backupValue = false;
			this.preview.update('<img src="'+this.getPreviewSrc(value)+'">');
            this.btnDelete.show();
			this.btnCrop.show();
            
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
			
			if (!keepBackup && !this.backupValue) this.backupValue = this.getValue();
			if (!saveBackup) this.backupValue = false;
			
            if (this.backupValue) {
                this.preview.update('<img src="'+this.getPreviewSrc(this.backupValue)+'"><div style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.8); line-height: '+this.height+'px">'+Config.Lang.picToBeDeleted+'</div>');
            } else {
                this.preview.update('');
            }
            this.btnDelete.hide();
			this.btnCrop.hide();
        }
		
		if (this.backupValue) {
			this.btnRestore.show();
		} 
		else {
			this.btnRestore.hide();
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

        this.btnCounter = Ext.create('Ext.Button',{
            scope   : this,
            border    : false,
            style : 'background:url(images/counterclockwise-arrow.png);margin-right:5px;opacity:0;transition:0.5s;',
            width   : 30,
            height   : 30,
            handler : function() {
                this.doRotate(90);
            }
        });

        this.btnClockwise = Ext.create('Ext.Button',{
            scope   : this,
            border    : false,
            style : 'background:url(images/clockwise-arrow.png);opacity:0;transition:0.5s;',
            width   : 30,
            height   : 30,
            handler : function() {
                this.doRotate(-90);
            }
        });

        this.rotationPlate = Ext.create('Ext.Panel',{
            region    : 'south',
            height    : 35,
            border    : false,
            bodyStyle :'background:none',
            items     : [
                this.btnCounter,
                this.btnClockwise
            ]
        });

        this.preview = Ext.create('Ext.Panel',{
            layout  : 'border',
            border  : false,
            bodyCls :'chess',
            bodyStyle : 'text-align: center; display: table-cell; vertical-align: middle',
            region  : 'center',
            listeners: {
                'render' : {
                    scope: this,
                    fn: function(e) {
                        var clockwise = this.btnClockwise;
                        var counter = this.btnCounter;
                        e.getEl().on('mouseover', function(event) {
                            clockwise.getEl().setStyle('opacity', '0.7');
                            counter.getEl().setStyle('opacity', '0.7');
                            if (clockwise.getEl().contains(event.target)) {
                                clockwise.getEl().setStyle('opacity', '1');
                            } else if (counter.getEl().contains(event.target)) {
                                counter.getEl().setStyle('opacity', '1');
                            }
                        });
                        e.getEl().on('mouseout', function() {
                            clockwise.getEl().setStyle('opacity', '0');
                            counter.getEl().setStyle('opacity', '0');
                        });
                    }
                }
            },
            items   : [
                this.rotationPlate
            ]
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
                this.setValue('', false, true);
            }
        });

        this.btnCrop = Ext.create('Ext.Button',{
            text    : _('Кадрировать'),
            iconCls : 'icon-crop',
            hidden  : true,
            scope   : this,
            width   : '100%',
            handler : function() {
				this.getCropWindow().show().setValue( this.getRealValue(this.backupValue?this.backupValue:this.getValue()) );
            }
        });

        this.btnRestore = Ext.create('Ext.Button',{
            text  : _('Восстановить'),
			iconCls : 'icon-undo',
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
                    region    : 'east',
					width     : 200,
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
						this.btnCrop,
                        this.btnDelete, 
                        this.btnRestore,						
                        this.info
                    ]
                }
            ]
        });    
    
    },
	
    getCropWindow : function() {
		if (!this.cropWindow) {
			this.cropWindow = Ext.create('Cetera.window.ImageCrop');
			this.cropWindow.on('crop',function(value){
				this.setValue(value, true);
			},this);
        }
        return this.cropWindow;      
    },
    
  	onDestroy: function(){
        if (this.window) this.window.destroy();
        if (this.uploadWindow) this.uploadWindow.destroy();
		if (this.cropWindow) this.cropWindow.destroy();
        this.callParent(arguments);
  	},

    doRotate: function(direction) {
        Cetera.Ajax.request({
            url: 'include/action_files.php?action=rotate&file='+this.getValue()+'&rotang='+direction,
            timeout: 1000000,
            success: function() {
                this.setValue(this.getValue());
            },
            scope: this
        });
    }
});