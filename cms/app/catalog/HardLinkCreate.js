Ext.require('Cetera.field.Folder');

Ext.define('Cetera.catalog.HardLinkCreate', {

    extend:'Ext.FormPanel',
    
        fieldDefaults : {
            labelAlign: 'right', 
            labelWidth: 105
        },
        
    waitMsgTarget: true,
    bodyStyle:'padding:5px 5px 0; background: none',
    defaults   : { anchor: '0' },
    defaultType: 'textfield',
    border: false,
    monitorValid: true,
	tree: false,
    items: [
        {
            xtype: 'folderfield',
            id: 'link_id',
            name: 'node_id',
            fieldLabel: Config.Lang.linkTo,
            value: 0,
            path: '',
            nolink: 1,
            rule: 7,
            allowBlank: false,
            nodeValue: true
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
								action: 'hard_link_create', 
								parent_id: this.tree.getSelectedId(),
                                parent_node_id: this.tree.getSelectionModel().getLastSelected().get('node_id')
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
        this.win.setHeight(100);
        this.win.add(this);
        this.win.setTitle(Config.Lang.newLink);
        this.win.doLayout();
        this.win.show();        
        this.callParent();
    }
    
});
