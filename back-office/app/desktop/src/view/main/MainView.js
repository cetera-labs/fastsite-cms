Ext.define('Cetera.view.main.MainView', {
    extend: 'Ext.Container',
    xtype: 'mainview',
    controller: 'mainviewcontroller',
    layout: 'border',
    items: [   
    {
        region:'west',
        id:'west-panel',
        split:true,
        stateId: 'stateMainViewport',
        stateful: true,			
        width: 350,
        minSize: 175,
        maxSize: 400,
        border: false,
        margins:'0 0 0 5',
        layout: 'border',
        items: [ 
            {
                id:'main_navigation',
                xtype: 'mainnavigation',
                region: 'center',
                height: '50%',
            },            
            {
                xtype: 'panel',
                region:'south',
                height: '50%',
                layout:'fit',
                split: true,
                stateId: 'stateMainStructure',
                stateful: true,						
                
                items:  [{
                    xtype: 'tabpanel',
                    border: false,
                    activeTab: 0,
                    deferredRender:false,                           
                    items: [
                        {
                            id: 'main_tree',
                            xtype: 'maintree',
                        },
                    ]
                }]
            } 
        ]
    },   
    
    { xtype: 'headerview', reference: 'headerview', region: 'north', docked: 'top',    weight: -2 },
    {
        id:'main_tabs',
        xtype: 'tabpanel',
        region:'center',
        deferredRender:false,
        activeTab:0,
        enableTabScroll:true,
        defaults: {
            autoScroll:true,
            closable:true
        },
        listeners: {
            tabchange : function( tp , tab ) {
                tab.updateLayout();
                Cetera.getApplication().buildBoLink();
            },
            remove : function() {
                Cetera.getApplication().buildBoLink();
            },
            beforetabchange : function(tp, newTab, currentTab) { 
                if (currentTab && currentTab.content) currentTab.content.fireEvent('deactivate');
                if (newTab && newTab.content) newTab.content.fireEvent('activate');            
            }
        }
    },
  ]
});