Ext.define('Cetera.field.VisualTemplate', {

    extend:'Cetera.field.Panel',
	requires: 'Cetera.widget.Panel',
	
	alias : 'widget.visualtemplate',
	autoScroll : true,
		
	getPanel : function() {
		
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
				
        return Ext.create('Cetera.field.VisualTemplatePanel',{
			width:  '100%',
            height: this.height,
			tbar: [
				{
					iconCls: 'icon-plus',
					text:    Config.Lang.addWidget,
					menu:    menu
				}
			], 
			listeners: {
				'drop': function() {
					this.buildValue();
				},
				scope: this
			}
        });
		
	},
	
    actionAddWidget : function(item) {
        
        this.addWidget({
            widgetName: item.widgetName
		}, false);

    },
	
    addWidget : function(params, collapsed) {
		params.hideSaveButton = true;
		params.containerId = this.getId();
        var widget = Cetera.widget.Panel.getWidgetPanel(params, collapsed);
        if (!widget) return false;
        this.panel.add(widget);
		return widget;

    },	

	buildValue: function() {
		var value = [];
		this.panel.items.each(function(w) {
			var params = w.getParams()||{};
			params.name = w.widgetName;
			value[value.length] = params;
		}, this);		
		this.setValue(Ext.JSON.encode(value), true);
	},
	
	getSubmitData: function() {
		this.buildValue();
		return this.callParent();
	},
	
	setValue: function(value, internal) {	
		this.callParent(arguments);
		if (value && !internal) {
			var data = Ext.JSON.decode(value);
			Ext.Array.each(data, function(val) {
				this.addWidget({
					widgetName: val.name,
					widgetTitle: val.widgetTitle,
					widgetDisabled: val.widgetDisabled,
					params: val
				}, true);
			}, this);			
		}
	}

});

Ext.define('Cetera.field.VisualTemplatePanel', {
	extend:'Ext.Panel',
	initComponent : function(){
						
		this.callParent();

		this.addEvents({
			validatedrop: true,
			beforedragover: true,
			dragover: true,
			beforedrop: true,
			drop: true
		});

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
	}	
});
