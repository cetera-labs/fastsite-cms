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
            html: 'Fastsite CMS'
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
            xtype: 'button',
			text: _('Сообщить об ошибке'),
            href: 'https://cetera.ru/support/default.php?project=CCD&lang='+Config.locale
        },		
		{
			iconCls: 'icon-help',
			xtype: 'button',
			text: _('Справка'),
			menu: [{
				text: _('Руководство пользователя'),
				href: 'https://cetera-labs.github.io/fastsite-cms/docs/user-guide',
				hrefTarget: '_blank'
			},{
				text: _('Руководство разработчика'),
				href: 'https://cetera-labs.github.io/fastsite/docs/developer-guide',
				hrefTarget: '_blank'
			},{
				text: _('Вопросы и ответы'),
				href: 'http://www.fastsite.ru/help/faq/',
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
