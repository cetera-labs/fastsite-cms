// Основная Панель виджета "Контейнер"
Ext.define('Cetera.widget.ContainerMain', {

    extend: 'Cetera.widget.Widget',
	requires: 'Cetera.widget.Panel',
    cls: '',
    bodyCls: '',
    autoScroll : true,
    draggable: false,
    collapsible: false,
    margin: 0, 
    header: {
        height: 40,
        cls: 'x-container-header'
    },
    
    widgetName: 'Container',  
	
	params: {},
    
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
       
        this.tbar = [
            {
                iconCls: 'icon-plus',
                text:    Config.Lang.addWidget,
                menu:    menu
            }
        ]; 
                    
        this.callParent();

        this.addEvents({
            validatedrop: true,
            beforedragover: true,
            dragover: true,
            beforedrop: true,
            drop: true
        });
        
        this.on('drop', function() {
        
            widgets = [];
            this.items.each(function(w) {
                widgets.push(w.widgetId)
            }, this);

            Ext.Ajax.request({
                url: '/cms/include/action_set_widget.php',
                params: {
                    widgetName: this.widgetName,
                    id: this.widgetId,
                    'widgets[]': widgets
                }
            });
        
        }, this); 
		
		if (this.widgets) {
			Ext.Array.each(this.widgets, function(params) {
				this.addWidget(params, true);
			}, this); 
		}

    },
    
    refreshTitle : function() {
    
        var params = this.getParams();
    
        var t = '';
        if (params.widgetTitle) {
            t = t + '<b>'+ params.widgetTitle + '</b>';
        } else {
            t = t + '<b>'+ this.widgetName + '</b>';
        }
        if (params.widgetAlias) t = t + ' [' + params.widgetAlias + ']';
        t = t + ' [ID:' + this.widgetId + ']';
        this.setTitle(t);
    },

    // private
    initEvents : function(){
        this.callParent();
        this.dd = Ext.create('Cetera.ux.ContainerDropZone', this);
    },

    // private
    beforeDestroy : function() {
        if (this.dd) this.dd.unreg();
        this.callParent();
    },
    
    isValid : function() {
        return true;
    },
    
    getParams : function() { 
		params = this.params;
        params.widgetTitle = this.widgetTitle;
        params.widgetAlias = this.widgetAlias;
        return params;
    },

    setParams : function(params) {
		this.params = params;
        this.widgetTitle = params.widgetTitle;
        this.widgetAlias = params.widgetAlias;
    },    
    
    actionAddWidget : function(item) {
        
        var widget = this.addWidget({
             widgetName: item.widgetName,  
             widgetId: 0
        });
        if (!widget) return false;
        widget.load( true );

    },
    
    addWidget : function(params, collapsed) {  
        params.containerId = this.widgetId;
        var widget = Cetera.widget.Panel.getWidgetPanel(params, collapsed);
        if (!widget) return false;
        this.add(widget);
        return widget;
    }

});

Ext.define('Cetera.widget.ContainerMainProperties', {
    extend : 'Cetera.widget.WidgetProperties',
    height: 150,
    title: Config.Lang.addArea,
    
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
            },{
				fieldLabel: _('Шаблон'),
				xtype: 'widgettemplate',
				widget: 'Container'
			}]
        });
        
        this.callParent();
    }
    
});