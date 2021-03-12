Ext.require('Cetera.field.Folder');

Ext.define('Cetera.catalog.LinkCreate', {

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
            name: 'typ',
            fieldLabel: Config.Lang.linkTo,
            value: 0,
            path: '',
            nolink: 1,
            rule: 7,
            allowBlank: false
        },{
            fieldLabel: Config.Lang.name,
            name: 'name',
            allowBlank: false
        },{
            fieldLabel: 'Alias',
            name: 'alias',
            allowBlank: false,
            regex: /([\-\_a-zA-Z0123456789]+)/i
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
							url:'/cms/include/action_catalog.php', 
							params: {
								action: 'cat_create', 
								server:0, 
								parent: this.tree.getSelectedId(),
								link: 1
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
        this.win.setHeight(150);
        this.win.add(this);
        this.win.setTitle(Config.Lang.newLink);
        this.win.doLayout();
        this.win.show();        
        this.callParent();
    }
    
});
