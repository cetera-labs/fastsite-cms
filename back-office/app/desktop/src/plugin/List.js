Ext.define('Cetera.plugin.List', {

    extend: 'Ext.grid.Panel',
    requires: 'Cetera.model.Plugin',

    initComponent: function(){
    
        this.enableAction = Ext.create('Ext.Action', {
            iconCls: 'x-fa fa-toggle-on',  
            text: Config.Lang.do_on,
            disabled: true,
            scope: this,
            handler: function(widget, event) {
                var rec = this.getSelectionModel().getSelection()[0];
                this.call('enable');
            }
        });
        
        this.disableAction = Ext.create('Ext.Action', {
            iconCls: 'x-fa fa-toggle-off',
            text: Config.Lang.do_off,
            disabled: true,
            scope: this,
            handler: function(widget, event) {
                this.call('disable');
            }
        });       
        
        this.deleteAction = Ext.create('Ext.Action', {
            iconCls: 'x-fa fa-trash',
            text: Config.Lang.remove,
            disabled: true,
            scope: this,
            handler: function(widget, event) {

                var form = new Ext.FormPanel({
                    defaultType: 'checkbox',
                    bodyStyle:'padding:5px 5px 0; background: none',
                    border: false,
                    margins: '5 0 0 0',
                    items: [
                        { boxLabel: 'удалить данные', name: 'data' }
                    ]
                });
        
                var wnd = new Ext.Window({
                    title: Config.Lang.r_u_sure,
                    width:300,
                    height:100,
                    bodyBorder: false,
                    plain:true,
                    layout:'fit',
                    modal: true,
                    resizable: false,
                    items: [form],
                    buttons: [{
                        text: Config.Lang.ok,
                        scope: this,
                        handler: function() { 
                            var action = 'delete';
                            if (form.getForm().getValues().data)
                                action = 'delete_data';
                                
                            this.call(action, function(){
                                Cetera.getApplication().reload();
                            });
                            
                            wnd.close();
                        }
                    },{
                        text: Config.Lang.cancel,
                        handler: function() { wnd.close(); }
                    }]
                });
                wnd.show();

            }
        });
    
        Ext.apply(this, {
        
            border: false,
            hideHeaders: true,
            cls: 'plugins-grid',

            store: Ext.create('Ext.data.JsonStore', {
                model: 'Cetera.model.Plugin',
                proxy: {
                    type: 'ajax',
                    url: '/cms/include/data_plugins.php',
                    reader: {
                        type: 'json',
                        rootProperty: 'rows'
                    }
                }
            }),
            
            dockedItems: [{
                xtype: 'toolbar',
                items: [
                    {
                        tooltip: Config.Lang.reload,
                        iconCls:'x-fa fa-sync',
                        handler: function() { this.store.load(); },
                        scope: this
                    }, '-',
                    this.enableAction,
                    this.disableAction,
                    this.deleteAction, 
                    '-',
                    /*{
                        text: Config.Lang.addPlugin,
                        icon: 'images/16X16/pack.gif',
                        handler: function() { 
							Ext.create('Cetera.plugin.Add').show();
						},
                        scope: this
                    }*/
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
                this.enableAction,
                this.disableAction,
                this.deleteAction
            ]
        });
        
        this.store.load();
        
        this.callParent(arguments);
              
        this.getSelectionModel().on({
            selectionchange: function(sm, selections) {
                if (selections.length) {
                    this.deleteAction.enable();
                    var rec = selections[0];
                    if (rec.get('disabled')) {
                        this.enableAction.enable();
                        this.disableAction.disable();
                    } else {
                        this.enableAction.disable();
                        this.disableAction.enable();
                    }
                    if (rec.get('composer')) {
                        this.deleteAction.disable();
                    }
                    else {
                        this.deleteAction.enable();                        
                    }
                } else {
                    this.deleteAction.disable();
                    this.enableAction.disable();
                    this.disableAction.disable();
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
            
        if (record.get('disabled'))
            value = '<span class="x-fa fa-toggle-off"></span> ' + value;
            else value = '<span class="x-fa fa-toggle-on"></span> ' + value;
    
        return Ext.String.format(
            '<div><b>{0}</b>&nbsp;{1}{5}{3}</div><div class="x-grid-rowbody ">{2}</div><div class="x-grid-rowbody ">{4}</div>', 
            value, 
            record.get('version')?record.get('version'):'', 
            record.get('description'),
            record.get('disabled')?(' (' + Config.Lang.off + ')'):'',
            record.get('composer')?'[COMPOSER]':''
        );
    },
    
    call: function(action, callback) {
    
        var rec = this.getSelectionModel().getSelection()[0];
        if (!rec) return;
    
        Ext.Ajax.request({
            url: '/cms/include/action_plugins.php',
            params: { 
                action: action, 
                'plugin': rec.get('id')
            },
            scope: this,
            success: function(resp) {
                this.store.load({
                    callback: callback
                });
            }
        });
    }

});
  