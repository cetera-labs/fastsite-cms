Ext.define('Cetera.field.RichMatSet', {

    extend: 'Cetera.field.Panel',
    
    hideLabel: true,
        
    validate : function() {
      
        this.panel.removeBodyCls('error');
        
        var valid = true;
      
        var value = [];
        this.panel.items.each(function(item, index, len){
            var f = item.getForm();
            if (item.material_id || f.isDirty()) {
                var v = f.getValues();
                v['id'] = item.material_id;
                value.push(v);
            }
            if (!f.isValid()) valid = false;
        },this); 
        
        this.setValue(Ext.JSON.encode(value)); 

        if (!valid) this.panel.addBodyCls('error');
        
        return valid;   
    },
    
    onAddItem : function(rec) { 
        var item = Ext.create('Cetera.RichMatsetMaterial' + this.mat_type);
        if (rec) {
            item.material_id = rec.getId();
            item.getForm().setValues(rec.getData());
        }
        this.panel.add(item);
    },
    
    getPanel : function() {
           
        return Ext.create('Cetera.RichMatSetContainer', {
           
            tbar: [{
                xtype:'button',
                iconCls:'icon-new',
                tooltip:Config.Lang.add,
                handler: function() {
                    this.onAddItem(false);
                },
                scope: this
            }]
            
        });
    },    
        
    initComponent : function(){
    
        this.callParent(arguments);
    
        this.store.each(function(rec) {
            this.onAddItem(rec);
        }, this);
        
    } 
    
});

Ext.define('Cetera.RichMatSetContainer', {
    extend: 'Ext.Panel',
        
    layout: 'anchor',
    
    defaults: {
        anchor: '100%'
    },    

    autoScroll: true,
    
    initEvents : function(){
        this.callParent();
        this.dd = Ext.create('Cetera.ux.ContainerDropZone', this);
    },

    beforeDestroy : function() {
        if (this.dd) this.dd.unreg();
        this.callParent();
    }     
});