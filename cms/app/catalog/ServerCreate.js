Ext.define('Cetera.catalog.ServerCreate', {

    extend:'Ext.FormPanel',
    
        fieldDefaults : {
            labelAlign: 'right', 
            labelWidth: 100
        },

    bodyStyle:'padding:5px 5px 0; background: none',
    width: 350,
    defaults   : { anchor: '0' },
    defaultType: 'textfield',
    border: false,
    monitorValid: true,  
	tree: false,	
   
    items: [
        {
            fieldLabel: Config.Lang.name,
            name: 'name',
            allowBlank:false
        }, 
        {
            fieldLabel: Config.Lang.domainName,
            name: 'alias',
            allowBlank:false,
            regex: /(([\-\w]+\.)+\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*)/i
        },
        {
            xtype: 'combo',
            fieldLabel: Config.Lang.materialType,
            valueField: 'id',
            displayField: 'describDisplay',
            name: 'typ',
            store: new Ext.data.JsonStore({
                fields: ['id', 'describDisplay'],
                autoLoad: true,  
                proxy: {
                    type: 'ajax',
                    url: 'include/data_types.php?linkable=1&empty=1',
                    reader: {
                        type: 'json',
                        root: 'rows'
                    }
                }
            }),
            triggerAction: 'all',
            selectOnFocus:true,
            editable: false,
            allowBlank: false             
        }
    ], 
	
	initComponent: function() {
		
		Ext.apply(this, {
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
						form.submit({
							url:'include/action_catalog.php', 
							params: {
								action: 'cat_create', 
								server:1, 
								parent: 0
							},
							waitMsg: Config.Lang.wait,
							scope: this,
							success: function(form, action) {
								this.tree.reloadNode(this.tree.getStore().getNodeById('item-0-1'));
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
        this.win.setHeight(160);
        this.win.add(this);
        this.win.setTitle(Config.Lang.createServer);
        this.win.doLayout();
        this.win.show();        
        this.callParent();
    }

});