Ext.define('Cetera.fo.Widget', {
    extend: 'Ext.container.ButtonGroup',
    
    requires: 'Cetera.widget.Panel',
	
    title: 'Параметры виджета',
    
    widgetsContainer: null,
        
    initComponent : function(){

        var items = [];
        Ext.Array.each(Config.widgets, function(value) {
            items.push({
                scope: this,
                handler: this.actionAddWidget,
                icon: value.icon,
                text: value.describ,
                widgetName: value.name
            });
        }, this); 
       
        var menu = Ext.create('Ext.menu.Menu', {
            items: items
        });	

        if (this.widget.hasCls('x-cetera-widget__container-child')) {
            this.items = [
                {
                    iconCls: 'icon-setup',
                    text: 'Настроить виджет',
                    handler: function(btn) { 
                        var me = btn.up('buttongroup');
                        var w = me.widget;
                        if (!w) return;
                        
                        var params = JSON.parse(w.getAttribute( 'data-params' ));
                        
                        params.widgetName = params.name;
                        var widgetPanel = me.getWidgetPanel(params);
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
                                        
                                        me.reloadWidget(me.widget);                                    
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
                    handler: function(btn) {
                        var me = btn.up('buttongroup');
                        var w = me.widget;
                        if (!w) return;   
                        var prev = w.prev('.x-cetera-widget__container-child');           
                        if (!prev) return;
                        w.insertBefore(prev);
                        me.saveContent();                
                    }
                }, 
                {
                    iconCls: 'icon-down',
                    text: 'Ниже',     
                    handler: function(btn) {
                        var me = btn.up('buttongroup');
                        var w = me.widget;
                        if (!w) return;   
                        var next = w.next('.x-cetera-widget__container-child');           
                        if (!next) return;
                        w.insertAfter(next);
                        me.saveContent();
                    }            
                },          
                {
                    iconCls: 'icon-delete',
                    text: 'Удалить',     
                    handler: function(btn) {
                        var me = btn.up('buttongroup');
                        var w = me.widget;
                        if (!w) return;   
                        Ext.MessageBox.confirm(_('Удалить виджет'), _('Вы уверены?'), function(btn) {
                            if (btn == 'yes') {
                                me.widgetsContainer = w.parent('.x-cetera-widget__container');
                                w.remove();
                                me.saveContent();
                            }
                        }, this);                
                    }            
                }           
            ];            
        }
        else {
            this.items = [];
        }
        
        this.items.push({
            iconCls: 'icon-plus',
            text:    'Добавить виджет',
            menu:    menu
        });
    
        this.callParent();
    
    },
    
    actionAddWidget: function(item) {
                
        var widgetPanel = this.getWidgetPanel({
            widgetName: item.widgetName
        });   

        var win = Ext.create('Ext.Window', {
            plain: true,
            width: 700,
            modal: true,
            layout: 'fit',
            border: false,
            items: widgetPanel,
            title: widgetPanel.widgetName ,
            icon : widgetPanel.icon,
            buttongroup: this,
            
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
                        
                        var newWidget = Ext.DomHelper.insertBefore(me.widget, {
                            tag: 'div',
                            cls: 'x-cetera-widget x-cetera-widget__container-child'
                        }, true);
                        
                        newWidget.set({ 
                            'data-params': JSON.stringify(params),
                            'data-class': 'Cetera.fo.Widget'
                        });
                        newWidget.on('mouseenter', widgetEnter);
                        
                        me.reloadWidget(newWidget);
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

    },    
    
    getWidgetPanel: function(params) {
        return Cetera.widget.Panel.getWidgetPanel(params, false);      
    },
    
    reloadWidget: function(widget) {
        
        var params = JSON.parse(widget.getAttribute( 'data-params' ));
        
        Ext.Ajax.request({
            url: '/cms/include/widget.php',
            params: {
                widget: params.name,
                params: widget.getAttribute( 'data-params' )
            },
            success: function(response, opts) {
                widget.setHTML(response.responseText);
            },             
            scope: this
        });         
    },

    saveContent: function() {
        if (!this.widgetsContainer) {
            var w = this.widget;
            if (!w) return;            
            this.widgetsContainer = w.parent('.x-cetera-widget__container');
        }
        var section = this.widgetsContainer.getAttribute( 'data-section' );
        var data = [];
        this.widgetsContainer.select('.x-cetera-widget__container-child').each(function(el, widgets){
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