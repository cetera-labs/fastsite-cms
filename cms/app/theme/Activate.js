Ext.define('Cetera.theme.Activate', {

    extend:'Ext.Window',  
	
	isDirty: false,
	closeAfterSave: false,
	
	theme: null,
	serverId: null,
              
    initComponent: function(){
		
		var wait = Ext.Msg.wait(_('Загрузка ...'),_('Подождите'),{
			modal: true
		});
    	
        this.serversPanel = Ext.create('Ext.grid.GridPanel', {
            enableHdMenu     : false,
            enableColumnMove : false,
            enableColumnResize: false,
            region: 'west',
			width: 300,
            
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
						var sel = 0;
						if (this.serverId) {
							var rec = this.serversPanel.getStore().getById(this.serverId);
							if (rec) sel = [rec];
						}
						this.serversPanel.getSelectionModel().select(sel);
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
        
		var data = {
			disabled: true,
			region: 'center',
			margin: '0 0 0 5',
			theme: this.theme,
			listeners: {
				 scope: this,
				 'dirtychange': function() {
					 if (!this.configPanel.isDisabled()) {
						this.isDirty = true;
					 }
				 }               
			} 
		}
		
		try{
			this.configPanel = Ext.create('Theme.'+this.theme.get('id')+'.Config', data);
		} catch(e) {
			this.configPanel = Ext.create('Cetera.theme.ConfigEmpty', data);
		}
        
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
            width:'80%',
            height: '80%',
            resizable: false,
            layout: 'border',
            items: [this.serversPanel, this.configPanel],
            buttons: [this.applyButton],
            bodyPadding: 5                      
        });
        
        this.callParent(arguments);
		wait.close();
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