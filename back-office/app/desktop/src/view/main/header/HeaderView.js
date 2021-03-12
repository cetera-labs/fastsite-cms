Ext.define('Cetera.view.main.header.HeaderView', {
  extend: 'Ext.toolbar.Toolbar',
  height: 50,
  xtype: 'headerview',
  cls: 'headerview',
  defaults: {
    ui:'toolbutton-toolbar'
  },
  items: [
    {
        xtype: 'component',
        cls: 'app-header-title',
        html: 'Fastsite CMS'
    },  
    '->',
    {
        xtype: 'button',
        text: _('Перейти на сайт'),
        hrefTarget: '_top',
        href: "/"
    },	
    {
        iconCls: 'x-fa fa-bug',
        xtype: 'button',
        text: _('Сообщить об ошибке'),
        href: 'https://cetera.ru/support/default.php?project=CCD&lang='+Config.locale
    },		
    {
        iconCls: 'x-fa fa-question-circle',
        xtype: 'button',
        text: _('Справка'),
        menu: [{
            text: _('Руководство пользователя'),
            href: 'https://cetera-labs.github.io/fastsite-cms/docs/user-guide',
            hrefTarget: '_blank'
        },{
            text: _('Руководство разработчика'),
            href: 'https://cetera-labs.github.io/fastsite-cms/docs/developer-guide',
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
        iconCls: 'x-fa fa-sign-out-alt',
        text: _('Выход'),
        hrefTarget: '_top',
        href: "/cms/logout.php?redirect=index.html"
    }   
  ]
});
