Ext.define('Cetera.theme.Install', {

    extend:'Ext.Window',    
          
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
                url: 'include/action_themes.php',
                autoLoad: true,
                params: {
                    action: 'install',
                    theme: this.themeName
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