Ext.define('Cetera.panel.Structure', {
	
	extend: 'Ext.Panel',
	requires: [
		'Cetera.panel.StructureTree',
		'Cetera.panel.MaterialsByCatalog'
	],

	border: false,	
	layout: 'border',
	padding: 5,
	bodyCls: 'x-window-body-default',        
    cls: 'x-window-body-default',
	style: 'border: none',
	
	initComponent : function() {
		
		var tree = Ext.create('Cetera.panel.StructureTree',{
			region:'north',
            height: '40%',
            split:true,
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
				height: '60%',
				title: _('Материалы'),
				region:'center',
				border: true,	
				xtype: 'materials-by-catalog',
				split:true,
				stateId: 'stateStructureMaterials',
				stateful: true,
				structureTree: tree
			} 
		];
		
		this.callParent();
	}
	
});