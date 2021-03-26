Ext.define('Cetera.field.FileEditable', {

    extend:'Cetera.field.Panel',
	
	alias : 'widget.fileselect_editable',
	
	beautify: false,
	minify: false,	
        	    
    setValue : function(value) {
    
		this.fileInput.setValue(value);
		if (value) {
			this.editBtn.show();
		} else {
			this.editBtn.hide();
		}
		
		this.callParent(arguments);
    },
    
    getPanel : function() {

		if (this.editOnly) this.protectedValue = false;
	
        this.window = Ext.create('Cetera.window.FileEdit', {
			width: 700,
			height: 500,
			modal: true,
			
			beautify: this.beautify,
			minify: this.minify,			
			
            listeners: {
                 scope: this,
                 'beforeSave': function( wnd, editor ) {
                      if ( this.protectedValue == wnd.file ) {
							var parts = wnd.file.split('.');
							var i = 0;
							if (parts.length > 1) i = parts.length - 2;
							if (this.getFileSuffix) {
								parts[i] += this.getFileSuffix();
							}
							else {
								parts[i] += '_user';
							}
							
							wnd.file = parts.join('.');
							this.fileInput.setValue(wnd.file);
					  }
                 }               
            } 
        }); 
    
		this.fileInput = Ext.create('Cetera.field.File',{
			region: 'center',
			name: this.name,
			value: this.value,
			disabled: this.editOnly,
            listeners: {
                 scope: this,
                 'change': function( field, newValue, oldValue, eOpts ) {
                      if (newValue != oldValue) {
						  this.setValue( newValue );
						  this.resetBtn.setVisible( this.protectedValue && this.protectedValue != this.value );
					  }
                 }               
            }       			
		});
		
		this.editBtn = Ext.create('Ext.Button',{
			region: 'east',
			tooltip: Config.Lang.edit,
			iconCls: 'icon-edit',
			hidden: true,
			border: false,
			scope: this,
			handler: function() {
				this.window.editFile( this.fileInput.getValue() );
			}
		});	

		this.resetBtn = Ext.create('Ext.Button',{
			region: 'east',
			tooltip: Config.Lang.resetDefault,
			iconCls: 'icon-undo',
			hidden: !this.protectedValue || this.protectedValue == this.value,
			border: false,
			scope: this,
			handler: function() {
				this.fileInput.setValue(  this.protectedValue );
			}
		});			
	
        return new Ext.Panel({
            layout    : 'border',
            border    : false,
            bodyStyle : 'background: none',
			height: 10,
            items: [
                this.fileInput
                // Запрещаем изменять файлы из админки 
                // https://pm.cetera.ru/browse/CCD-1335
				//,this.editBtn
				//,this.resetBtn
            ]
        });    
    
    },
	
  	onDestroy: function(){
        this.window.destroy();
		this.fileInput.destroy();
		this.editBtn.destroy();
		this.resetBtn.destroy();
    	this.callParent(arguments);
  	}        

});