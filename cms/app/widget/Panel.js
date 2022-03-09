Ext.require('Cetera.widget.List');

Ext.define('Cetera.widget.Panel', {

    extend: 'Ext.Panel',
    border: false,
    
    layout: {
        type: 'border'
    },
	
    statics: {
		getWidgetPanel : function(params, collapsed) {

			var info = Ext.Array.findBy(Config.widgets, function(item){
				return item.name == params.widgetName;
			},this);

			if (!info) return false;
			 
			if (info.ui)
				cls = info.ui;
				else cls = 'Cetera.widget.'+info.name;

			//Ext.require(cls);
			//if (!Ext.ClassManager.get(cls)) {
			//     cls = 'Cetera.widget.Widget';
			//}
			
			var widget = Ext.create(cls, {
				 widgetId:     params.widgetId || 0,
				 widgetName:   info.name,
				 containerId:  params.containerId || 0,
				 widgetDescrib:info.describ,
				 widgetAlias:  params.widgetAlias,
				 widgetTitle:  params.widgetTitle,
				 widgetDisabled:params.widgetDisabled,
				 icon:         info.icon,
				 collapsed:    collapsed,
				 saveButton:   params.hideSaveButton?false:true,
			});
			
			if (params.params)
				widget.setParams(params.params);
				else widget.setParams({});
			 
			return widget; 
			 
		} 
    },	
    
    initComponent : function(){
       
        this.widgets = Ext.create('Ext.Panel',{
            
            region: 'center',
            bodyCls: 'widgets',
        
            autoScroll : true,
            margins: '5 5 5 0',
            layout: 'fit',
            border: false,
            
            addContainer : function(params) {
                var container = Ext.create('Cetera.widget.ContainerMain', params);
                this.add(container); 
                return container;
                
            }

        });
        
        this.store = new Ext.data.JsonStore({
            autoDestroy: true,
            fields: ['id','widgetName','widgetAlias','widgetTitle','params','widgetDisabled','protected'],
            proxy: {
                type: 'ajax',
                url: '/cms/include/data_widgets.php',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            }
        });        
        
        this.menu = Ext.create('Ext.grid.Panel', {
            tbar: [
                {
                    iconCls: 'icon-reload',
                    toolTip: Config.Lang.reload,
                    handler: function() {
                        this.store.load();
                    },
                    scope: this
                },{
                    iconCls: 'icon-plus',
                    text:    Config.Lang.addArea,
                    handler: this.actionAddContainer,
                    scope: this
                }
            ],       
            region: 'west',
            margins: 5,

            store: this.store,
            hideHeaders: true,
            columns: [
                { dataIndex: 'widgetTitle', flex: 1 },
            ],
            width: 300
        });               
        
        this.items = [this.menu, this.widgets];
        
        this.menu.getSelectionModel().on({
            'selectionchange' : function(sm){
                if (!sm.hasSelection()) return;
                var sel = sm.getSelection()[0];
                
                this.widgets.removeAll(true);
                this.widgets.setLoading(true);
                
                Ext.Ajax.request({
                    url: '/cms/include/action_get_widget.php',
                    params: {
                        id:	sel.get('id')   
                    },
                    success: function(response) {
                        var obj = Ext.decode(response.responseText);
                        this.widgets.setLoading(false);  
                        var container = this.widgets.addContainer(obj);   
                        
                        container.on({
                            'delete': function() {
                                this.store.load();
                            },
                            'saveSuccess': function(opt) {
                                this.menu.getSelectionModel().getSelection()[0].set('widgetTitle', opt.widget.getParams().widgetTitle);
                            },                    
                            scope:this
                        });                                              
                    },
                    failure: function() {
                        this.widgets.setLoading(false);
                    },
                    scope: this
                });                 
                
                
            },
            scope:this
        });        
        
        this.callParent();
        
        this.store.load();

    },
    
    actionAddContainer : function() {
    
        this.setLoading( true );
    
        Ext.Ajax.request({
            url: '/cms/include/action_set_widget.php',
            params: {
                id: 0,
                container_id: 0,
                widgetAlias: 'area',
                widgetName: 'Container',
                widgetTitle: _('Область')
            },
            success: function(response) {
                var obj = Ext.decode(response.responseText);
                this.setLoading( false ); 
                this.store.load({
                    callback: function(records, operation, success) {
                         this.menu.getSelectionModel().select(records.length - 1);
                    },
                    scope: this
                });    
            },
            failure: function() {
                this.setLoading( false );
            },
            scope: this
        });     
    
    },

});