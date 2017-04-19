Ext.define('Cetera.Viewport', {
    extend: 'Ext.container.Viewport',
    requires: [
        'Ext.layout.container.Border'
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
            width: 300,
            minSize: 175,
            maxSize: 400,
            border: false,
            margins:'0 0 0 5',
            layout: 'border',
            items: [ 
				navigation,
                treeContainer
            ]
        }        
    ],
    
    initComponent : function(config) {    
    
        this.callParent(arguments);
    
    }

});