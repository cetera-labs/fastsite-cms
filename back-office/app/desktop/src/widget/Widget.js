Ext.define('Cetera.widget.Widget', {
    extend: 'Ext.panel.Panel',

    closable: true,
    cls: 'x-widget-panel',
    bodyCls: 'x-window-body-default',
    draggable: {moveOnDrag: false},
    
    margin: '5',
    
    widgetId: 0,
    widgetDescrib: _('Виджет'),
    widgetName: 'Widget',
    containerId: 0,
    inWindow: false,
    collapsible: true,
	saveButton: true,
       
    initComponent : function() {
    
        if (!this.containerId && this.widgetName != 'Container') {
        
            this.margin = 0;
            this.draggable = false;
            this.closable = false;
            this.header = false;
            this.inWindow = true;
            
            var formfields = [{
                name: 'widgetTitle',
                fieldLabel: _('Заголовок'),
                allowBlank: true
            }];               
        
        } else {
        
            var formfields = [{
                xtype: 'checkbox',
                boxLabel: Config.Lang.do_off,
                name: 'widgetDisabled',
                inputValue: 1,
                uncheckedValue: 0
            },{
                name: 'widgetTitle',
                fieldLabel: _('Заголовок'),
                allowBlank: true
            }];    
            
            if (this.widgetProtected) {
                this.closable = false;
            }                  
    
            this.tools = [];
    
            if (!this.widgetProtected && Ext.ClassManager.get(this.$className + 'Properties') ) {
                 this.tools.push({
                    type:'gear',
                    tooltip: Config.Lang.setup,
                    handler: this.showProperties,
                    scope: this
                });
            }
			
            if (this.widgetName != 'Container' && this.saveButton) {      
                
                this.dockedItems = [{
                    xtype: 'toolbar',
                    dock: 'bottom',
                    ui: 'default',
                    defaults: {minWidth: 100},
                    items: [
                        { xtype: 'component', flex: 1 },
                        {
                            xtype: 'button',
                            text: Config.Lang.save,
                            handler: function() {
                                if (this.isValid()) this.save();
                            },
                            scope: this
                        }
                    ]
                }];
                 
            }
        
        }
        
        if (this.widgetName != 'Container') {    
        
            if (!this.form) {
                            
                if (this.formfields) 
                     formfields = formfields.concat(this.formfields);
            
                this.form = Ext.create('Ext.form.Panel',{
              
                    layout: 'anchor',
                    border: false,
                    defaults: {
                        anchor: '100%',
                        hideEmptyLabel: false
                    },                       
                    defaultType: 'textfield',
                    bodyStyle:'background: none',
                    bodyPadding: 5,
                    fieldDefaults: {
                        labelWidth: 130
                    },
                    
                    items: formfields
                });
                
            } 
            
            this.items = this.form; 
                    
        }
    
        this.on('beforeclose',this.closeQuery,this);
        
        this.callParent();
        
        this.on('saveSuccess', this.refreshTitle, this); 
        
    },
    
    // private
    beforeDestroy : function() {
        if (this.form) this.form.destroy();
        this.callParent();
    },
       
    afterRender : function() {
    
        this.callParent();
        this.refreshTitle();
    
    },
    
    showProperties : function() {
    
        this.propWin = Ext.create(this.$className + 'Properties', {
            title: Config.Lang.properties + ': ' + this.title,
            widget: this
        });
        this.propWin.show();
    
    },
    
    refreshTitle : function() {

        var t = '';
        if (this.widgetTitle) {
            t = t + '<b>'+ this.widgetTitle + '</b>';
            t = t + ' (' + this.widgetDescrib + ')';
        } else {
            t = t + '<b>'+ this.widgetDescrib + '</b>';
        }

		if (this.widgetId) {
			t = t + ' [ID:' + this.widgetId + ']';
		}
        
        if (this.widgetDisabled) 
            t = t + ' <span style="color:red">'+_('отключен') +'</span>';
        
        this.setTitle(t);
    },
    
    closeQuery : function() {
        Ext.MessageBox.confirm(Config.Lang.delete, Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes') this.deleteWidget();
        }, this);
        return false;
    },
    
    deleteWidget : function() {
		
		if (this.saveButton) {
    
			this.setLoading(true);
			
			Ext.Ajax.request({
				url: '/cms/include/action_del_widget.php',
				params: {
					widgetName: this.widgetName,
					widgetId: this.widgetId,
					containerId: this.containerId
				},
				success: function(response){
					this.setLoading(false);
					this.fireEvent('delete');
					this.destroy();
				},
				failure: function(response){
					this.setLoading(false);
				},
				scope: this
			});
			
		}
		else {
			
			this.fireEvent('delete');
			this.destroy();
			
		}
    
    },
    
    isValid : function() {
        return this.form.getForm().isValid();
    },
    
    getParams : function() {
        var params = {};
        if (this.form) {
            params = this.form.getForm().getValues();
            this.widgetTitle = params.widgetTitle;
            this.widgetDisabled = params.widgetDisabled;
        }       
         
        return params;
    },

    setParams : function(params) {
    
        params.widgetTitle = this.widgetTitle;
        params.widgetDisabled = this.widgetDisabled;
    
        if (this.form) 
            this.form.getForm().setValues(params);
    },
    
    save : function() {
    
        var params = this.getParams()||{};
        
        params.id = this.widgetId;
        params.widgetName = this.widgetName;
        params.container_id = this.containerId;
        
        this.setLoading( true );
    
        Ext.Ajax.request({
            url: '/cms/include/action_set_widget.php',
            params: params,
            success: function(response) {
                var obj = Ext.decode(response.responseText);
                this.widgetId = obj.id;
                this.widgetDescrib = obj.widgetDescrib;
                this.fireEvent('saveSuccess', {
                    widget: this
                });
                this.setLoading( false );
                this.refreshTitle();     
            },
            failure: function() {
                this.setLoading( false );
            },
            scope: this
        }); 
        
    },
    
    load : function( save ) {
    
        this.setLoading( true );
    
        Ext.Ajax.request({
            url: '/cms/include/action_get_widget.php',
            params: {
                id: this.widgetId,
                widgetName: this.widgetName,
                container_id: this.containerId            
            },
            success: function(response) {
                this.setLoading( false ); 
                
                var obj = Ext.decode(response.responseText);                
                if (Ext.isObject(obj.params)) this.setParams(obj.params); 
         
                if ( save ) this.save();
                this.fireEvent('loadSuccess');
            },
            failure: function() {
                this.setLoading( false );
            },
            scope: this
        }); 
        
    }

});

Ext.define('Cetera.widget.WidgetProperties', {
    extend : 'Ext.Window',
    modal: true,
    width: 400,
    height: 300,
    resizable: false,
    layout: 'fit',
    containerId: 0,
    form: null,

    initComponent : function(){
    
        this.items = this.form;
    
        this.buttons = [
            {
                text: Config.Lang.ok,
                scope: this,
                handler: this.save
            },{
                text: Config.Lang.cancel,
                scope: this,
                handler: this.close
            }
        ];
    
        this.callParent();
    },
    
    save : function() {
        if (!this.form) return;
        var form = this.form.getForm();
        if (form.isValid()) {
            this.widget.setParams(form.getFieldValues());
            this.widget.save();
            this.close();
        }
    },
    
    show : function() {
        this.callParent();
        var p = this.widget.getParams();
        this.form.getForm().setValues(p);        
    }    
});