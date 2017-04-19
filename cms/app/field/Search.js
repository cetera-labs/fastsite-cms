Ext.define('Cetera.field.Search', {

    extend:'Ext.form.field.Trigger',

    initComponent : function(){
        this.callParent();
        
        this.on('specialkey', function(f, e){
            if(e.getKey() == e.ENTER){
                this.onTrigger2Click();
            }
        }, this);
    },

    validationEvent:false,
    validateOnBlur:false,
    trigger1Cls:'x-form-clear-trigger',
    trigger2Cls:'x-form-search-trigger',

    width:180,
    hasSearch : false,
    paramName : 'query',

    onTrigger1Click : function(){
        this.setValue('');
        if(this.hasSearch){
            var o = {start: 0};
            this.store.proxy.extraParams = this.store.proxy.extraParams || {};
            this.store.proxy.extraParams[this.paramName] = '';
            this.store.load({params:o});
            this.hasSearch = false;
        }
    },

    onTrigger2Click : function(){
        var v = this.getRawValue();
        if(v.length < 1){
            this.onTrigger1Click();
            return;
        }
        var o = {start: 0};
        this.store.proxy.extraParams = this.store.proxy.extraParams || {};
        this.store.proxy.extraParams[this.paramName] = v;
        this.store.load({params:o});
        this.hasSearch = true;
    }
});