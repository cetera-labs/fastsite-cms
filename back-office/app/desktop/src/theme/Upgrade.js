Ext.define('Cetera.theme.Upgrade', {

    extend:'Ext.Window',  

	content: '',
	themeName: '',
          
    initComponent: function(){
    
        Ext.apply(this, {
            title: Config.Lang.pluginInstall + '"' + this.themeName + '"',
            autoHeight: true,
            autoShow: true,
            modal: true,
            width:500,
            closable: false,
            resizable: false,
            bodyPadding: 10,
            html: Config.Lang.loading,
			
            loader: {
                url: '/cms/include/action_themes.php',
                autoLoad: true,
                params: {
                    action: 'install',
                    theme: this.themeName,
					content: this.content,
                },
				ajaxOptions: {
					timeout: 600000
				},
                listeners: {
					
                    load: {
        
                        fn: function(l,response) {

                            this.addDocked(Ext.create('Ext.Button', {
                                 text: (response.status == 200)?Config.Lang.ok:Config.Lang.close,
                                 dock: 'bottom',
                                 scope: this,
                                 handler: function() {
                                    this.close();
                                    if (response.status == 200) {
                                        Cetera.getApplication().reload();
                                    }
                                 }
                            }));
                            
                        },
                        scope: this

                    }
                    
                }                
            }  
						
        });
        
        this.callParent(arguments);
    }
});