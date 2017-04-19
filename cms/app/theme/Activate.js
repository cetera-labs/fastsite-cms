Ext.define('Cetera.theme.Activate', {

    extend:'Ext.Window',  
	
	isDirty: false,
	closeAfterSave: false,
              
    initComponent: function(){
    	
        this.serversPanel = Ext.create('Ext.grid.GridPanel', {
            enableHdMenu     : false,
            enableColumnMove : false,
            enableColumnResize: false,
            region: 'center',             
            
            store: new Ext.data.JsonStore({
                autoDestroy: true,
                autoLoad: true,
                fields: ['id','name','active','config'],
                proxy: {
                    type: 'ajax',
                    api: {
                        read    : 'include/data_theme.php',
                        update  : 'include/data_theme.php'
                    },                   
                    reader: {
                        type: 'json',
                        root: 'rows'
                    },
                    writer: {
                        type: 'json',
                        writeAllFields: true,
                        root: 'rows',
                        encode: true
                    },                    
                    extraParams: {
                        'theme': this.theme.get('id')
                    }                    
                },
				listeners: {
					scope: this,
					'datachanged': function() {
						this.isDirty = false;
						this.setLoading( false );
						if (this.closeAfterSave) this.close();
					},
					'load': function() {
						this.serversPanel.getSelectionModel().select(0);
					}
				}
            }),
            
            columns: [
          		{
                  header: Config.Lang.server, 
                  flex: 1,
                  dataIndex: 'name'
              },{
                  xtype: 'checkcolumn',
                  header: Config.Lang.used,
                  dataIndex: 'active',
                  width: 100
              }
          	]
        });  
        
        this.serversPanel.on({
            'select' : function(sm, rec){
                if ( !this.configPanel ) return;				
				this.configPanel.setServer(rec);
            },
            'deselect' : function(sm, rec){
                if ( !this.configPanel ) return;
                rec.set( 'config', this.configPanel.getForm().getValues() );
            },            
            scope:this
        });                   
    
        var items = [this.serversPanel];
        this.configPanel = null;
        
		this.configPanel = Ext.create('Theme.'+this.theme.get('id')+'.Config', {
			disabled: true,
			region: 'east',
			margin: '0 0 0 5',
			width: 550,
			theme: this.theme,
			listeners: {
				 scope: this,
				 'dirtychange': function() {
					 if (!this.configPanel.isDisabled()) {
						this.isDirty = true;
					 }
				 }               
			} 
		});
		items.push(this.configPanel);
        
        this.applyButton = Ext.create('Ext.Button', {
            text : Config.Lang.apply,
            scope: this,
            handler: this.saveChanges
        });
		
		this.on('beforeclose', function(){
			
			if (!this.isDirty) return;
			
			Ext.Msg.show({
				 title: '',
				 msg: Config.Lang.saveChanges,
				 buttons: Ext.Msg.YESNOCANCEL,
				 icon: Ext.Msg.QUESTION,
				 scope: this,
				 fn: function(btn) {
					 if (btn == 'no') {
						 this.doClose();
					 }
					 if (btn == 'yes') {
						 this.closeAfterSave = true;
						 this.saveChanges();
					 }
				 }
			});
			return false;
			
		}, this);
    
        Ext.apply(this, {
            title: Config.Lang.theme + ' "' + this.theme.get('title') + '"',
            autoHeight: true,
            autoShow: true,
            modal: true,
            width:800,
            height:500,
            resizable: false,
            layout: 'border',
            items: items,
            buttons: [this.applyButton],
            bodyPadding: 5                      
        });
        
        this.callParent(arguments);
    },
	
	saveChanges: function() {
		
				this.setLoading( true );
                var sm = this.serversPanel.getSelectionModel();
                if (this.configPanel && sm.hasSelection()) {
                    sm.getSelection()[0].set( 'config', this.configPanel.getForm().getValues() );
                }
                
                this.serversPanel.store.update();		
		
	}
}); 