Ext.define('Cetera.field.LinkSet2', {

    extend: 'Cetera.field.LinkSet',

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

    }
	
});