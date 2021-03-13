Ext.define('Cetera.field.Trigger', {

    extend:'Ext.form.Text',
    
    fieldSubTpl: [ // note: {id} here is really {inputId}, but {cmpId} is available
            '<input id="{id}-display" style="width:100%" value="{displayValue}" type="text" readonly="readonly"',
            '<tpl if="name"> name="{name}-display"</tpl>',
            '<tpl if="disabled"> disabled="disabled"</tpl>',
            ' class="{fieldCls} {typeCls} {typeCls}-{ui} {editableCls} {inputCls} {fixCls}" />',
            '<input id="{id}" type="hidden" readonly="readonly"',
            '<tpl if="name"> name="{name}"</tpl>',
            '<tpl if="value"> value="{[Ext.util.Format.htmlEncode(values.value)]}"</tpl>',
            '<tpl if="disabled"> disabled="disabled"</tpl> />',
        {
            disableFormats: true
        }
    ],
    
    initComponent : function(){
        if (!this.displayValue) this.displayValue = '';
        this.callParent();
    },
    
    getDisplayId: function() {
        return this.displayId || (this.displayId = this.id + '-inputEl-display');
    },
    
    onRender: function() {
        this.callParent();
        this.setDisplayValue(this.displayValue);
    },
    
    setDisplayValue: function(value) {
        this.displayValue = value;

        if (this.el) {
            var displayEl = this.el.getById(this.getDisplayId());
            if (displayEl) displayEl.dom.value = value;
        }
    },
	
    renderActiveError: function() {
        var me = this,
            hasError = me.hasActiveError();
			
        if (this.el)
		{
            var displayEl = this.el.getById(this.getDisplayId());
            if (displayEl) {
				displayEl[hasError ? 'addCls' : 'removeCls'](me.invalidCls + '-field');
			}
        }			

		this.callParent();
    }
    
});