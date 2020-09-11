Ext.define('Cetera.fo.Widget', {
    extend: 'Ext.container.ButtonGroup',
    
    require: 'Cetera.widget.Panel',
	
    title: 'Параметры виджета',
    
	items: [
		{
			iconCls: 'icon-setup',
			text: 'Настроить виджет',
			handler: function(btn) { 
				var me = btn.up('buttongroup');
				var w = me.widget;
				if (!w) return;
				
				var params = JSON.parse(w.getAttribute( 'data-params' ));
                
                var widgetPanel = Ext.create('Cetera.widget.'+params.name, {
                     widgetId:     0,
                     widgetName:   params.name,
                     containerId:  0,
                     widgetDescrib:params.describ,
                     widgetAlias:  params.widgetAlias,
                     widgetTitle:  params.widgetTitle,
                     widgetDisabled: false,
                     collapsed:    false,
                     saveButton:   false
                });
                
                widgetPanel.setParams(params);             

                var win = Ext.create('Ext.Window', {
                    plain: true,
                    width: 700,
                    modal: true,
                    layout: 'fit',
                    border: false,
                    items: widgetPanel,
                    title: widgetPanel.widgetName ,
                    icon : widgetPanel.icon,
                    buttongroup: me,
                    
                    buttons : [
                        {
                            text: _('ОК'),
                            handler: function() {
                            
                                var window = this.up('window');
                                var widgetPanel = window.items.getAt(0);
                                
                                if (!widgetPanel.isValid()) return;
                                
                                var params = widgetPanel.getParams();
                                params.name = widgetPanel.widgetName;                                
                                
                                var me = window.buttongroup;
                                me.widget.set({ 'data-params': JSON.stringify(params) });
                                me.saveContent();
                                
                                window.close();

                            }
                        },{
                            text: _('Отмена'),
                            handler: function() {
                                this.up('window').close();
                            }
                        }                    
                    ]
                    
                }).show();                
				
			}
		}, 
        {
			iconCls: 'icon-up',
			text: 'Выше',            
        }, 
        {
			iconCls: 'icon-down',
			text: 'Ниже',            
        }, 
        {
			iconCls: 'icon-delete',
			text: 'Удалить',            
        }
	],

    saveContent: function() {
		var w = this.widget;
		if (!w) return;
        var container = w.parent('.x-cetera-widget__container');
        var section = container.getAttribute( 'data-section' );
        var data = [];
        container.select('.x-cetera-widget__container-child').each(function(el, widgets){
            Ext.Array.push(data, JSON.parse(el.getAttribute( 'data-params' )));
        });

        Ext.Ajax.request({
            url: '/cms/include/action_catalog.php',
            params: {
                action: 'cat_save',
                id: section,
                visual_constructor: JSON.stringify(data)
            },
            scope: this
        });         
    }
}); 