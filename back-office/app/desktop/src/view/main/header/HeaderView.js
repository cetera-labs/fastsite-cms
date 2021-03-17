Ext.define('Cetera.view.main.header.HeaderView', {
  extend: 'Ext.toolbar.Toolbar',
  
	requires: [
        'Ext.Responsive'
    ],  
  
  height: 50,
  xtype: 'headerview',
  cls: 'headerview',
  defaults: {
    ui:'toolbutton-toolbar'
  },
  items: [
    {
        iconCls: 'x-fa fa-bars',
        xtype: 'button',
        tooltip: _('Навигация'),
        hidden: true,
        responsiveConfig: {
            small: {
                hidden: false,
            },
            large: {
                hidden: true,
            }
        },   
        handler: function() {
            var westPanel = Ext.getCmp('west-panel');
            if (westPanel.isHidden()) {
                westPanel.show();
            } 
            else {
                westPanel.hide();
            } 
        } 
    },  
    {
        xtype: 'component',
        cls: 'app-header-title',
        html: 'Fastsite CMS',
        responsiveConfig: {
            small: {
                html: 'Fastsite',
            },
            large: {
                html: 'Fastsite CMS',
            }
        },        
    },  
    '->',
    {
        xtype: 'button',
        text: _('Перейти на сайт'),
        hrefTarget: '_top',
        href: "/",
        responsiveConfig: {
            small: {
                hidden: true
            },
            large: {
                hidden: false
            }
        },   
    },	
    {
        iconCls: 'x-fa fa-bug',
        xtype: 'button',
        tooltip: _('Сообщить об ошибке'),
        href: 'https://cetera.ru/support/default.php?project=CCD&lang='+Config.locale,
        responsiveConfig: {
            small: {
                text: '',
            },
            large: {
                text: _('Сообщить об ошибке'),
            }
        },   
        
    },		
    {
        iconCls: 'x-fa fa-question-circle',
        xtype: 'button',
        tooltip: _('Справка'),
        responsiveConfig: {
            small: {
                text: '',
            },
            large: {
                text: _('Справка'),
            }
        },        
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
        tooltip: _('Выход'),
        hrefTarget: '_top',
        href: "/cms/logout.php?redirect=ui.html",
        responsiveConfig: {
            small: {
                text: '',
            },
            large: {
                text: _('Выход'),
            }
        },        
    }   
  ]
});
