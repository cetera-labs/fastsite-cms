Ext.define('Cetera.main.Header', {
    extend: 'Ext.Container',

    id: 'app-header',
    height: 40,
    padding: '0 10',
    
    layout: {
        type: 'hbox',
        align: 'middle'
    },
    initComponent: function() {
        this.items = [{
            xtype: 'component',
            id: 'app-header-title',
            html: 'Cetera CMS'
        },{
            xtype: 'component',
            id: 'app-header-site',
			cls: 'header-item',
            html: '<a href="/">'+ Config.Lang.toFrontOffice +'</a>',
			flex: 1
        },{
            xtype: 'component',
            id: 'app-header-logout',
            html: '<a href="logout.php?redirect=index.php">'+ Config.Lang.logout +'</a>'
        }];

        this.callParent();
    }
});
