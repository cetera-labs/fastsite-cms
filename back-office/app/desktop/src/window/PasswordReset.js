Ext.define('Cetera.window.PasswordReset', {
    extend: 'Cetera.window.LockingWindow',
    
    title: _('Восстановление пароля'),

    items: [
        {
            xtype: 'form',
            defaultButton : 'loginButton',
            autoComplete: true,
            bodyPadding: '20 20',
            cls: 'auth-dialog auth-dialog-login',
            header: false,
            width: 415,
            
            url: '/cms/include/action_login.php',
            
            layout: {
                type: 'vbox',
                align: 'stretch'
            },

            defaults : {
                margin : '5 0'
            },

            items: [
                {
                    xtype: 'label',
                    cls: 'lock-screen-top-label',
                    text: _('Введите email')
                },
                {
                    xtype: 'textfield',
                    cls: 'auth-textbox',
                    height: 55,
                    name: 'recover',
                    hideLabel: true,
                    allowBlank: false,
                    emptyText: 'user@example.com',
                    vtype: 'email',
                    triggers: {
                        glyphed: {
                            cls: 'trigger-glyph-noop auth-email-trigger'
                        }
                    }
                },
                {
                    xtype: 'button',
                    scale: 'large',
                    //ui: 'soft-blue',
                    formBind: true,
                    iconAlign: 'right',
                    iconCls: 'x-fa fa-angle-right',
                    text: _('Сброcить пароль'),
                    listeners: {
                        click: 'onResetClick'
                    } 
                },
                {
                    xtype: 'button',
                    text: _('Вернуться'),
                    cls: 'link-button',
                    listeners: {
                        click: 'onLoginButton'
                    }                     
                }
            ]

        }
    ],

    initComponent: function() {
        this.addCls('user-login-register-container');
        this.callParent(arguments);
    },
    
    submitForm: function() {
    
        var f = this.form.getForm();
    
        Config.setLocale(f.getValues().locale);
    
        f.submit({
            scope: this,
            waitMsg: _('Подождите...'),
            success: function(form, action) {
                this.fireEvent('login', action.result);
                Config.user = action.result.user;
                if (action.result.locale && Config.locale != action.result.locale) {
                    Config.locale = action.result.locale;
                }
                alert('login success!');
            }
        });        
    }
}); 