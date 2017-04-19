// Панелька виджета
Ext.require('Cetera.field.Ace');

Ext.define('Cetera.widget.Simple', {
    extend: 'Cetera.widget.Widget',
    
    formfields : [{
        xtype: 'acefield',
        name: 'template',
        hideLabel: true,
        anchor: '100%',
        height: 210,
        allowBlank: false
    }]
    
});

Ext.define('Cetera.widget.SimpleProperties', {
    extend : 'Cetera.widget.WidgetProperties',
    width: 600,
    
    initComponent : function(){
    
        this.form = Ext.create('Ext.form.Panel',{
            border: false,
            layout: 'anchor',
            defaultType: 'acefield',
            bodyPadding: 10,
            items: {       
                hideLabel: true,
                name: 'template',
                allowBlank: false,
                anchor: '100% 100%'
            }
        });
        
        this.callParent();
    }
    
});