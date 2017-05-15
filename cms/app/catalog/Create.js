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
                    name: 'tablename',
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
				}
            ],
            
            buttons: [
                {
                    xtype: 'button',
                    text: Config.Lang.ok,
                    formBind: true,
                    disabled:true,  
                    handler: function() {
                        var form = this.up('form').getForm();
                        var tree = Ext.getCmp('main_tree');
                        form.submit({
                            url:'include/action_catalog.php', 
                            params: {
                                action: 'cat_create', 
                                server:0, 
                                parent: tree.getSelectedId()
                            },
                            waitMsg: Config.Lang.wait,
                            scope: this,
                            success: function(form, action) {
                                tree.reloadNode(tree.getSelectionModel().getLastSelected());
                                this.up('form').win.hide();
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
        this.win.setHeight(160);
        this.win.add(this);
        this.win.setTitle(Config.Lang.newCatalog);
        this.win.doLayout();
        this.win.show();        
        this.callParent();
    }
    
});