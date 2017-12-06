Ext.define('Cetera.Viewport', {
    extend: 'Ext.container.Viewport',
    requires: [
        'Ext.layout.container.Border',
		'Cetera.main.Navigation',
		'Cetera.main.Tree'
    ],

    layout: 'border',

    items: [
        Ext.create('Cetera.main.Header',{
            region: 'north',
            margins:'0 0 5 0',
        }),    
        tabs,
        {
            region:'west',
            id:'west-panel',
            split:true,
            stateId: 'stateMainViewport',
            stateful: true,			
            width: 300,
            minSize: 175,
            maxSize: 400,
            border: false,
            margins:'0 0 0 5',
            layout: 'border',
            items: [ 
				Ext.create('Cetera.main.Navigation'),
				Ext.create('Ext.Panel', {
					region:'center',
					layout:'fit',
					items:  Ext.create('Ext.TabPanel',{  
						border: false,
						activeTab: 0,
						deferredRender:false,                           
						items: [
							mainTree
						]
					})
				}) 
            ]
        }        
    ],
    
    initComponent : function(config) {    
    
        this.callParent(arguments);
    
    }

});