Ext.define('Cetera.theme.List', {                                                                

    extend: 'Ext.grid.Panel',
    requires: 'Cetera.model.Theme',
	
	settingsWindow: null,
	contentSettingsWindow: null,

    initComponent: function(){
    
        this.activateAction = Ext.create('Ext.Action', {
            iconCls: 'icon-on',  
            text: Config.Lang.activate + ' / ' + Config.Lang.setup,
            disabled: true,
            scope: this,
            handler: function(widget, event) {
                var rec = this.getSelectionModel().getSelection()[0];

                Ext.create('Cetera.theme.Activate',{
                    theme: rec
                });                

            }
        });
        
        this.upgradeAction = Ext.create('Ext.Action', {
            iconCls: 'icon-unpack', 
            text: Config.Lang.upgrade,
            disabled: true,
            scope: this,
            handler: function(widget, event) {
            
                var rec = this.getSelectionModel().getSelection()[0];
                if (!rec) return;   

                Ext.MessageBox.confirm(Config.Lang.upgrade, Config.Lang.r_u_sure, function(btn) {
                    if (btn == 'yes') {
						Ext.create('Cetera.theme.Upgrade',{
							themeName: rec.get('id'),
							content: 0
						});						
					}
                }, this);				
            }
        });        
        
        this.deleteAction = Ext.create('Ext.Action', {
            iconCls: 'icon-delete2', 
            text: Config.Lang.remove,
            disabled: true,
            scope: this,
            handler: function(widget, event) {
            
                Ext.MessageBox.confirm(Config.Lang.delete, Config.Lang.r_u_sure, function(btn) {
                    if (btn == 'yes') this.call('delete');
                }, this);

            }
        });
		
        this.renameAction = Ext.create('Ext.Action', {
            iconCls: 'icon-settings', 
            tooltip: _('Конфигурация'),
            disabled: true,
            scope: this,
            handler: function(widget, event) {
				this.getSettingsWindow().show();
            }
        });	

        this.uploadAction = Ext.create('Ext.Action', {
            iconCls: 'icon-upload', 
            tooltip: _('Выгрузить контент в MarketPlace'),
            hidden: !Config.developerKey,
			disabled: true,
            scope: this,
            handler: function(widget, event) {
				
				this.getContentSettingsWindow().show();

            }
        });			
    
        Ext.apply(this, {
        
            border: false,
            hideHeaders: true,
            cls: 'plugins-grid',

            store: Ext.create('Ext.data.Store', {
                model: 'Cetera.model.Theme'
            }),
            
            dockedItems: [{
                xtype: 'toolbar',
                items: [
                    {
                        tooltip: Config.Lang.reload,
                        iconCls: 'icon-reload',
                        handler: function() { this.reload(); },
                        scope: this
                    }, '-',
                    this.activateAction,
                    this.upgradeAction,
                    this.deleteAction, '-',
					this.renameAction, 
					this.uploadAction,
					'-',				
                    {
                        text: Config.Lang.addTheme,
                        icon: 'images/image_add.png',
                        handler: function() { Ext.create('Cetera.theme.Add').show(); },
                        scope: this
                    }
                ]
            }],
            
            viewConfig: {
                stripeRows: true,
                listeners: {
                    itemcontextmenu: {
                        fn: function(view, rec, node, index, e) {
                            e.stopEvent();
                            this.contextMenu.showAt(e.getXY());
                            return false;
                        },
                        scope: this
                    }
                }
            },
            
            columns: [{
                text: 'Title',
                dataIndex: 'title',
                flex: 1,
                renderer: this.formatTitle
            }]
        });
        
        this.contextMenu = Ext.create('Ext.menu.Menu', {
            items: [
                this.activateAction,
                this.upgradeAction,'-',
                this.deleteAction,'-',
				this.renameAction			
            ]
        });
        
        this.store.load();
        
        this.callParent(arguments);
              
        this.getSelectionModel().on({
            selectionchange: function(sm, selections) {
                if (selections.length) {
                    this.activateAction.enable(); 
					this.renameAction.enable();
					if (selections[0].get('repository') && !selections[0].get('disableUpgrade')) {
						this.upgradeAction.enable(); 
					}
					else {
						this.upgradeAction.disable(); 
					}					
					if (selections[0].get('disableUpgrade')) {
						this.deleteAction.disable();
					}
					else {
						this.deleteAction.enable();	
					}
					if (selections[0].get('developerMode')) {
						this.uploadAction.enable(); 
					}
					else {
						this.uploadAction.disable();  
					}					
                } else {
                    this.deleteAction.disable();
                    this.activateAction.disable();
                    this.upgradeAction.disable();
					this.renameAction.disable();
					this.uploadAction.disable();  
                }
            },
            scope: this
        });
		

    },

    /**
     * Title renderer
     * @private
     */
    formatTitle: function(value, p, record){
    
        var upgrade = '';
        if (record.get('upgrade')) {
            upgrade = '<img src="images/au.gif" align="absmiddle" /> ' + Config.Lang.upgradeAvail + ' (v' + record.get('upgrade') + ')';
        }
            
        return Ext.String.format(
            '<div><b>{0}</b>&nbsp;{1}</div><div class="x-grid-rowbody ">{2}</div><div class="x-grid-rowbody ">{3}</div>', 
            value, 
            record.get('version')?('v'+record.get('version')):'', 
            record.get('description'),
            upgrade
        );
    },
    
    call: function(action, callback) {
    
        var rec = this.getSelectionModel().getSelection()[0];
        if (!rec) return;
		
		this.setLoading(true);
    
        Ext.Ajax.request({
            url: 'include/action_themes.php',
            params: { 
                action: action, 
                'theme': rec.get('id')
            },
            scope: this,
            success: function(resp) {
                this.store.load({
                    callback: callback
                });
            },
            callback: function(response){
                this.setLoading(false);
            },
			
        });
    },
	
	getSettingsWindow: function() {
		
        var rec = this.getSelectionModel().getSelection()[0];
		
		if (!this.settingsWindow) {
			this.settingsWindow = Ext.create('Cetera.theme.Settings',{
				theme: rec,
				listeners: {
					'theme_update': function() {
						this.reload();
					},
					scope: this
				}
			});
		}
		return this.settingsWindow;
	},

	getContentSettingsWindow: function() {
		
        var rec = this.getSelectionModel().getSelection()[0];
		
		if (!this.contentSettingsWindow) {
			this.contentSettingsWindow = Ext.create('Cetera.theme.ContentSettings',{
				theme: rec,
				listeners: {
					'content_update': function() {
						this.reload();
					},
					scope: this
				}				
			});
		}
		return this.contentSettingsWindow;
	},	
	
	reload: function() {
		this.getSelectionModel().deselectAll();
		this.store.load();
	}

});
  