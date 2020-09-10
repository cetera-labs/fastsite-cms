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
        
		var data = {
			disabled: true,
			theme: this.theme,
            serverId: this.serverId,
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
        
        Ext.Ajax.request({
            url: '/cms/include/action_themes.php',
            params: {
                action: 'get_config',
                theme: this.theme.get('id'),
                server: this.serverId,
            },
            success: function(response){
                var obj = Ext.decode(response.responseText);
                this.configPanel.getForm().setValues( obj.config );
                this.configPanel.enable();
            },
            scope: this
        });        
        
        this.applyButton = Ext.create('Ext.Button', {
            text : _('Применить'),
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
            layout: 'fit',
            items: this.configPanel,
            buttons: [this.applyButton],
            bodyPadding: 5                      
        });
        
        this.callParent(arguments);
		wait.close();
    },
		
	saveChanges: function() {
		
        this.setLoading( true );
        
        Ext.Ajax.request({
            url: '/cms/include/action_themes.php',
            params: {
                action: 'save_config',
                theme: this.theme.get('id'),
                server: this.serverId,
                config: Ext.JSON.encode(this.configPanel.getForm().getValues())
            },
            success: function(response){
                this.setLoading(false);
            },
            failure: function(response){
                this.setLoading(false);
            },
            scope: this
        });        	
		
	}
}); 