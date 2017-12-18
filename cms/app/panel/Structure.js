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
		
		this.structure = Ext.create('Cetera.panel.StructureTree',{
			region:'center'
		});
		
		this.items = [
			this.structure,
			{
				height: '70%',
				title: _('Материалы'),
				region:'south',
				border: true,	
				xtype: 'structurematerials',
				collapsible: true,
				split:true,
				stateId: 'stateStructureMaterials',
				stateful: true,
				structureTree: this.structure
			} 
		];
		
		this.callParent();
	}
	
});