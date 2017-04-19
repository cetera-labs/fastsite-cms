Ext.define('Cetera.widget.Container', {

    extend: 'Widget',
    
    saveButton: true,
    
    initComponent : function(){
    
        this.form = Ext.create('Ext.form.Panel',{
      
            layout: 'anchor',
            border: false,
            defaults: {
                anchor: '100%'
            },
            defaultType: 'textfield',
            bodyStyle:'background: none',
            bodyPadding: 5,
            fieldDefaults: {
                labelWidth: 130
            },
            
            items: [{
                xtype: 'combo',
                fieldLabel: Config.Lang.area,
                valueField: 'widgetId',
                displayField: 'widgetTitle',
                name: 'widgetId',
                store: new Ext.data.JsonStore({
                    fields: ['widgetId','widgetTitle'],
                    url: '/cms/include/data_widgets.php?containers=1',
                    root: 'data',
                    autoSync: true,
                    autoLoad: true
                }),
                editable: false,
                triggerAction: 'all',
                selectOnFocus:true,
                allowBlank: false,
                listeners: {
                     scope: this,
                     'change': function( combo, newValue, oldValue, eOpts ) {
                          var dv = combo.getDisplayValue();
                          if (dv != newValue)
                              this.form.getForm().setValues({
                                  widgetTitle: dv
                              })
                     }               
                }       
            },{
                xtype: 'hiddenfield',
                name: 'widgetTitle'
            }],
        });
        
        this.items = this.form;
        
        this.callParent();
        
        this.tools = false;
    },
    
    isValid : function() {
        return this.form.getForm().isValid();
    },
    
    getParams : function() {
        var v = this.form.getForm().getValues();
        this.widgetId = v.widgetId;
        return v;
    },

    setParams : function(params) {
        params.widgetId = this.widgetId;
        this.form.getForm().setValues(params);
    }

});

// Окно настройки виджета "Контейнер"
Ext.define('Cetera.widget.ContainerProperties', {
    extend : 'WidgetProperties',
    height: 130,
    title: Config.Lang.addArea,
    widgetClass: 'WidgetContainer',
    
    initComponent : function(){
    
        this.form = Ext.create('Ext.form.Panel',{
            border: false,
            layout: 'anchor',
            waitMsgTarget: true,
            defaultType: 'textfield',
            bodyPadding: 10,
            defaults: {
                anchor: '100%'
            },
            items: [{
                fieldLabel: Config.Lang.name,
                name: 'widgetTitle',
                allowBlank: false
            },{
                fieldLabel: 'Alias',
                name: 'widgetAlias',
                regex: /^[\.\-\_A-Z0-9]+$/i
            }]
        });
        
        this.callParent();
    }
    
});