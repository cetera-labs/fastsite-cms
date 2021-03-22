Ext.define('Cetera.view.main.MainView', {
    extend: 'Ext.Container',
    xtype: 'mainview',
    
	requires: [
        'Ext.Responsive'
    ],
    
    responsiveFormulas: {
        small: 'width < 600',
        medium: 'width >= 600 && width < 800',
        large: 'width >= 800',
    },    
    
    controller: 'mainviewcontroller',
    layout: 'border',
    items: [   
    {
        region:'west',
        id:'west-panel',
       
        stateId: 'stateMainViewport',
        stateful: true,			
        width: 350,
        minSize: 175,
        maxSize: 400,
        border: false,
        margins:'0 0 0 5',
        layout: 'border',
        closeAction: 'hide',
        closable: false,
                
        responsiveConfig: {
            small: {
                width: '100%',
                floatable: true,
                hidden: true,
            },
            large: {
                margins:'0 0 0 5',
                floatable: false,
                width: 350,
                split:true,
                collapsed: false,
                collapsible: false,
                hidden: false,
            }
        },        
        items: [ 
            {
                id:'main_navigation',
                xtype: 'treelist',
                region: 'center',
                height: '50%',
                store: 'navigationMain',
                ui: 'nav',
                listeners: {
                    selectionchange: 'onNavigationTreeSelectionChange'
                }                
            },  
            {
                hidden: true,
                id: 'main_tree',
                xtype: 'maintree',
                region:'south',
                height: '50%',    
                split: true,
                stateId: 'stateMainStructure',
                stateful: true,	                
            }, 
        ]
    },   
    
    { xtype: 'headerview', reference: 'headerview', region: 'north', docked: 'top',    weight: -2 },
    {
        id:'main_panel',
        xtype: 'panel',
        layout: 'card',
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
            },
            beforetabchange : function(tp, newTab, currentTab) { 
                if (currentTab && currentTab.content) currentTab.content.fireEvent('deactivate');
                if (newTab && newTab.content) newTab.content.fireEvent('activate');            
            }
        }
    },
  ]
});