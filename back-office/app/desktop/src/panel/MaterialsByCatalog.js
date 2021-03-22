Ext.define('Cetera.panel.MaterialsByCatalog', {

    extend:'Ext.Panel',

    layout: 'border',
    
	requires: [
        'Ext.Responsive'
    ],    
    
    initComponent : function() {
        
        var tree = Ext.create('Cetera.view.main.Tree',{
            itemId: 'tree',
            xtype: 'maintree', 
            width: 500,
            floatable: true,
            closable: true,
            region: 'west',
            hidden: true,
            closeAction: 'hide',  
            padding: 5,
            responsiveConfig: {
                small: {
                    width: '100%',
                    padding: 5,
                },
                large: {
                    width: 500,
                    padding: '5 5 5 0',
                }
            },             
        });
        
        tree.expandPath('/root/item-0-1', 'id', '/', function(bSuccess, oLastNode) {
            if (bSuccess && oLastNode.firstChild) {
                tree.getSelectionModel().doSingleSelect(oLastNode.firstChild);
                tree.expandNode(oLastNode.firstChild);
            }
        });        
        
        this.items = [ 
            tree,    
            {
                xtype: 'materials-by-catalog',
                flex: 1,
                region: 'center',
                structureTree: tree,
            },       
        ];
        
		this.callParent(); 
    }        
    
});