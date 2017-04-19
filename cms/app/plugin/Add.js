Ext.define('Cetera.plugin.Add', {

    extend: 'Ext.Panel',
    requires: 'Cetera.model.Plugin',

    initComponent: function() {
    
        this.store = Ext.create('Ext.data.JsonStore', {
            model: 'Cetera.model.Plugin',
            proxy: {
                type: 'ajax',
                url:  'include/data_plugins_lib.php',
                reader: {
                    type: 'json',
                    root: 'rows'
                }
            }
        });   
    
        this.dataview = Ext.create('Ext.view.View', {            
            cls: 'plugins-lib',
            
            store: this.store,
            tpl  : Ext.create('Ext.XTemplate',
				'<div class="plugin">', 
					'<div class="panel x-panel-body-default" style="text-align: center">',                     
						'<a href="https://tochka.com/?referer1=50mrpCETERA&utm_source=pertners&utm_medium=50mrpCETERA" target="_blank" style="position: relative; color:#000; padding: 5px 10px; margin-top: 35px" class="x-btn x-btn-default-small x-noicon x-btn-noicon x-btn-default-small-noicon">Открыть счет в банке "Точка"</a>',
					'</div>',
				'</div>',
				'<div class="plugin">', 
					'<div class="panel x-panel-body-default" style="text-align: center;background-color: #efffef">',                     
						'<a href="https://ceteralabs.ru/webdevelopment/website/order-module" target="_blank" style="font-weight:bold;position: relative; color:#000; padding: 5px 10px; margin-top: 35px" class="x-btn x-btn-default-small x-noicon x-btn-noicon x-btn-default-small-noicon">Заказать модуль</a>',
					'</div>',
				'</div>',				
				'<tpl for=".">',
                    '<div class="x-plugin plugin">',
                        '<div class="panel {cls}">',
                            '<div class="right">',
                                '{version}{comp}{author}',
                            '</div>',                        
                            '<div class="button" id="button-{id}"></div>',
                            '<div class="title x-panel-header-text-container-default">{title}</div>{installed}',
                            '<div class="text">{description}</div>',
                        '</div>',
                    '</div>',
                '</tpl>'
            ),
                       
            installPlugin: function(pluginName) {
                Ext.create('Cetera.plugin.Install',{
                    pluginName: pluginName
                });               
            },
            
            prepareData: function(data) {
                if (data.version) data.version = '<b>'+Config.Lang.version+':</b> '+data.version+'<br>';
                if (data.author) data.author = '<b>'+Config.Lang.author+':</b> '+data.author+'<br>';
                
                data.comp = '<b>'+Config.Lang.needCms+':</b> ';
                if (data.cms_version_min) data.comp += Config.Lang.from + ' ' + data.cms_version_min + ' ';
                if (data.cms_version_max) data.comp += Config.Lang.to + ' ' + data.cms_version_max;
                if (!data.cms_version_max && !data.cms_version_min) data.comp += Config.Lang.any;  
                data.comp += '<br>';
                
                if (data.installed) { 
                    data.installed = ' ('+Config.Lang.installed+')';
                    data.cls = 'x-panel-body-default';
                } else {
                    data.installed = '';
                    data.cls = 'x-window-body-default';
                }
                
                return data;
            }     

        });
		
		this.dataview.on('refresh',function(){
		
                this.store.each(function(rec) {

                    if (!rec.get('installed') || rec.get('upgrade')) {
                        if (!rec.get('installed')){
                            var text = Config.Lang.install;
                            var action = 'install';
                        }
                        if (rec.get('upgrade')) {
                            var text = Config.Lang.upgrade;
                            var action = 'upgrade';
                        }
                        var disabled = false;
                        if (!rec.get('compatible')) disabled = true;
                        
                        Ext.create('Ext.Button',{
                            text: text,
                            plugin: rec.get('id'),
                            pluginTitle: rec.get('title'),
							compatible: rec.get('compatible'),
							compatible_message: rec.get('compatible_message'),
							
                            action: action,
                            scope: this,
                            handler: function(button) {

								if (!button.compatible) {
									if (button.compatible_message) Ext.MessageBox.alert(Config.Lang.install, button.compatible_message);
									return
								}
							
                                if (button.action == 'install') {
                                    Ext.MessageBox.confirm(
                                        Config.Lang.install, 
                                        Config.Lang.install+' "'+button.pluginTitle+'"<br>'+Config.Lang.r_u_sure, 
                                        function(btn) {
                                            if (btn == 'yes') this.installPlugin(button.plugin);
                                        }, 
                                        this
                                    );
                                } else {
                                    this.installPlugin(button.plugin);
                                }
                                
                            }
                        }).render(document.body, 'button-'+rec.get('id'));                        
                    }

                }, this);  
		
		},this.dataview);		
    
        Ext.apply(this, {
        
            border: false,
            
            layout: 'fit',
            items : this.dataview,
            
            dockedItems: [{
                xtype: 'toolbar',
                items: [
                    {
                        tooltip: Config.Lang.reload,
                        iconCls: 'icon-reload',
                        handler: function() { this.store.load(); },
                        scope:   this
                    }
                ]
            }]

        });
        
        this.store.load();
        
        this.callParent(arguments);
      
    }

});
  