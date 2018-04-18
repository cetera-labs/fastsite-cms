Ext.define('Cetera.theme.Add', {

    extend: 'Ext.window.Window',
	modal: true,
	width: '80%',
	height: '80%',
	title: _('Установить тему'),
	
    requires: 'Cetera.model.Theme',

    initComponent: function() {
    
        this.store = Ext.create('Ext.data.JsonStore', {
            model: 'Cetera.model.Theme',
            proxy: {
                type: 'ajax',
                url:  'include/data_themes_lib.php',
                reader: {
                    type: 'json',
                    root: 'rows'
                }
            }
        });   
		
        this.dataview = Ext.create('Ext.DataView', {
            store: this.store,
            loadingText: Config.Lang.wait,
            cls: 'plugins-lib',
            comp: this,
            tpl  : Ext.create('Ext.XTemplate',
					'<div class="panel x-panel-body-default" style="text-align: center;background-color: #efffef; margin: 5px">',                     
						'<a href="https://cetera.ru/webdevelopment/website/order-design/" target="_blank" style="font-weight: bold;position: relative; color:#000; padding: 5px 10px; margin-top: 20px" class="x-btn x-btn-default-small x-noicon x-btn-noicon x-btn-default-small-noicon">'+_('Заказ индивидуального дизайна')+'</a>',
					'</div>',
				'<br clear="all"><h1>'+_('Темы от Cetera')+'</h1>',					
                '<tpl for=".">',
					"<tpl if='this.isCommutinyBlock(general)'><br clear='all'><h1>"+_('Темы от сообщества')+"</h1></tpl>",
                    '<div class="plugin x-plugin">',
                        '<div class="panel {cls}">',
                            '<div class="right">',
                                '{version}{comp}{author}',
                            '</div>',                        
                            '<div class="button" id="button-{id}"></div>',
                            '<div class="title x-panel-header-text-container-default">{title}</div>{installed}',
                            '<div class="text">{description}</div>',
                        '</div>',
                    '</div>',
                '</tpl>',
				{
					isCommutinyBlock: function(general){
						if (!this.comminityBlock && !general) {
							this.comminityBlock = true;
							return true;
						}
						return false;
					},					
				}
            ),
			
            prepareData: function(data) {
                if (data.version) data.version = '<b>'+Config.Lang.version+':</b> '+data.version+'<br>';
                if (data.author) data.author = '<b>'+Config.Lang.author+':</b> '+data.author+'<br>';
                
                data.comp = '<b>'+Config.Lang.needCms+':</b> ';
                if (data.cms_version_min) data.comp += Config.Lang.from + ' ' + data.cms_version_min + ' ';
                if (data.cms_version_max) data.comp += Config.Lang.to + ' ' + data.cms_version_max;
                if (!data.cms_version_max && !data.cms_version_min) data.comp += Config.Lang.any;  
                data.comp += '<br>';
                
                if (data.installed) { 
                    data.installed = ' ('+Config.Lang.installed_f+')';
                    data.cls = 'x-panel-body-default';
                } else {
                    data.installed = '';
                    data.cls = 'x-window-body-default';
                }
                
                return data;
            },				

            install: function(name, title) {
				
				if (Config.contentExists) {
				
					Ext.MessageBox.confirm(
						Config.Lang.install, 
						Config.Lang.install+' "'+title+'"<br>'+Config.Lang.r_u_sure, 
						function(btn) {
							if (btn == 'yes') {
								
								Ext.create('Cetera.theme.Upgrade',{
									themeName: name
								});							
								
							}
						}, 
						this
					);	

				}
				else {
				
					Ext.create('Cetera.theme.Install',{
						themeName: name
					});
					
				}
            },
			
			upgrade: function(name) {
                Ext.create('Cetera.theme.Upgrade',{
                    themeName: name
                });               
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
                        
                        Ext.create('Ext.Button',{
                            text: text,
                            theme: rec.get('id'),
                            themeTitle: rec.get('title'),
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
                                    this.install(button.theme, button.themeTitle);
                                } else {
                                    this.upgrade(button.theme);
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
  