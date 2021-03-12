Ext.define('Cetera.catalog.Create', {

    extend:'Ext.FormPanel',
	
    requires: [
		'Cetera.field.MaterialType'
	],	
    
    labelAlign: 'right',
        fieldDefaults : { 
            labelWidth: 100
        },
    bodyStyle:'padding:5px 5px 0; background: none',
    width: 350,
    defaults   : { anchor: '0' },
    defaultType: 'textfield',
    border: false,
    monitorValid: true,
	
	tree: false,
    
    initComponent: function() {
    
        Ext.apply(this, {
            items: [
                {
                    fieldLabel: Config.Lang.name,
                    name: 'name',
                    allowBlank:false
                }, 
                {
                    fieldLabel: 'Alias',
                    name: 'alias',
                    allowBlank:false,
                    regex: /^[\.\-\_A-Z0-9]+$/i,
                },
				{
					xtype: 'materialtypefield',
					fieldLabel: _('Тип материалов'),
					name: 'typ',
					empty: 1,
					linkable: 1,
					allowBlank: false,
					value: this.materialsType  
				},
				{
					xtype: 'checkbox',
					boxLabel: _('создать индексный материал'),
					name: 'create_index',
					hideEmptyLabel: false,
					checked: Ext.state.Manager.get('catalogCreateIndex')
				}
            ],
            
            buttons: [
                {
                    xtype: 'button',
                    text: Config.Lang.ok,
                    formBind: true,
                    disabled:true,  
					scope: this,
                    handler: function() {
                        var form = this.getForm();
                        if (!this.tree) this.tree = Ext.getCmp('main_tree');
						
						Ext.state.Manager.set('catalogCreateIndex', form.getValues().create_index == 'on')
						
						
                        form.submit({
                            url:'/cms/include/action_catalog.php', 
                            params: {
                                action: 'cat_create', 
                                server:0, 
                                parent: this.tree.getSelectedId()
                            },
                            waitMsg: Config.Lang.wait,
                            scope: this,
                            success: function(form, action) {
                                this.tree.reloadNode(this.tree.getSelectionModel().getLastSelected());
                                this.win.hide();
                            }
                        });
                    }
                },{
                    xtype: 'button',
                    text: Config.Lang.cancel,
                    handler: function() { this.up('form').win.hide(); }
                }
            ]
          
        });
    
        this.callParent();
    },
    
    show : function() {

        this.win.setWidth(365);
        this.win.setHeight(170);
        this.win.add(this);
        this.win.setTitle(Config.Lang.newCatalog);
        this.win.doLayout();
        this.win.show();        
        this.callParent();
    }
    
});