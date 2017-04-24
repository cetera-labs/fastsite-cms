Ext.define('Cetera.main.Header', {
    extend: 'Ext.Toolbar',

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
        },
		{ xtype: 'tbseparator' },
		{
            xtype: 'button',
			text: Config.Lang.toFrontOffice,
			hrefTarget: '_top',
            href: "/"
        },	
		'->',
		{
			iconCls: 'icon-help',
			xtype: 'button',
			text: _('Справка'),
			menu: [{
				text: _('Руководство пользователя'),
				href: 'https://cetera.ru/forclients/cetera-cms-user-guide/',
				hrefTarget: '_blank'
			},{
				text: _('Руководство разработчика'),
				href: 'https://cetera.ru/forclients/cetera-cms-developer-guide/',
				hrefTarget: '_blank'
			},{
				text: _('Вопросы и ответы'),
				href: 'http://www.fastsite.ru/help/faq/',
				hrefTarget: '_blank'
			},{
				text:'API',
				href: 'https://cetera.ru/forclients/api/',
				hrefTarget: '_blank'
			}]
		},
		{ xtype: 'tbseparator' },
		{
            xtype: 'button',
            id: 'app-header-logout',
			text: Config.Lang.logout,
			hrefTarget: '_top',
            href: "logout.php?redirect=index.php"
        }
		];

        this.callParent();
    }
});
