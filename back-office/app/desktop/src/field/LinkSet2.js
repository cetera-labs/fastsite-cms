Ext.define('Cetera.field.LinkSet2', {

    extend: 'Cetera.field.LinkSet',
    
    alias : 'widget.linkset2',

    onAddItem: function() {
    
        if (!this.siteTree) {
            this.siteTree = Ext.create('Cetera.window.SiteTree', {
                from: this.from,
                materials : 1,
                matsort : 'name',
                nocatselect: 1,
                dontclose: 1 
            });
            this.siteTree.on('select', function(res) {
                this.addItem({id: res.type + '_' + res.id, name: res.name_to});
            },this);
        }
        this.siteTree.show(); 

    },
    
    getObjectByValue: function(value) {
        var a = value.split('_');
        Ext.Ajax.request({
            url: '/cms/include/data_object.php',
            params: { id: a[1], type: a[0] },
            scope: this,
            success: function(response, opts) {
                var rec = this.store.getById( value );
                if (rec) {
                    var res = Ext.decode(response.responseText);
                    rec.set('name', res.fields.name);
                }
            }           
        });        
        
        return {
            'id': value,
            'name': _('Загрузка ..')
        };
    }
	
});