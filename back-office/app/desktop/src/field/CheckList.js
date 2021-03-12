Ext.define('Cetera.field.CheckList', {

    extend:'Cetera.field.Panel',

    prepareValue: function() {
        var val = [];
        var sel = this.panel.getSelectionModel().getSelection();
        for (var i=0; i<sel.length; i++) val.push(sel[i].getId());
        this.setValue(Ext.JSON.encode(val), true);
    },
    
    getPanel : function() {
    
        return new Ext.grid.GridPanel({
            store: this.store,  
            selType: 'checkboxmodel',  
            columns: [
                    {id:'clf_name', header: "Name", dataIndex: 'name', flex:1}
            ],
            hideHeaders: true,
            height: 100
        });   
    
    },
    
    setValue : function(value, internal) {
    
        this.callParent(arguments);
        
        if (internal) return;
    
        var obj = Ext.JSON.decode(value, true);
        if (!obj) return;
        
        var sel = [];
        Ext.Array.each(obj, function(value) {
            if (value instanceof Object)
                var id = value.id;
                else var id = value;
                
            var rec = this.store.findRecord('id', id);
            rec.set('selected', true);
            sel.push(rec);
        }, this); 
        this.panel.getSelectionModel().select(sel);
        
    },
    
    initComponent : function(){
                
        this.callParent();

        this.panel.getSelectionModel().on('selectionchange', function() {
            this.prepareValue();
        }, this);
        
        this.panel.on('viewready', function() {
            var a = [];
            this.store.each(function(rec) {
                if (rec.get('selected')) a[a.length] = rec;
            }, this);
             
            this.panel.getSelectionModel().select(a);
        }, this);
        

    }
    
});