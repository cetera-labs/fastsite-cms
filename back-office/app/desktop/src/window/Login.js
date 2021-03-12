Ext.define('Cetera.window.Login', {
    extend: 'Cetera.window.LockingWindow',
    
    title: Config.appName + ' v' + Config.appVersion + ' - Login',

    items: [
        {
            xtype: 'form',
            defaultButton : 'loginButton',
            autoComplete: true,
            bodyPadding: '20 20',
            cls: 'auth-dialog auth-dialog-login',
            header: false,
            width: 415,
            
            layout: {
                type: 'vbox',
                align: 'stretch'
            },

            defaults : {
                margin : '5 0'
            },

            items: [
                {
                    xtype: 'textfield',
                    cls: 'auth-textbox',
                    name: 'login',
                    height: 55,
                    hideLabel: true,
                    allowBlank : false,
                    emptyText: 'Login',
                    triggers: {
                        glyphed: {
                            cls: 'trigger-glyph-noop auth-email-trigger'
                        }
                    }
                },
                {
                    xtype: 'textfield',
                    cls: 'auth-textbox',
                    height: 55,
                    hideLabel: true,
                    emptyText: 'Password',
                    inputType: 'password',
                    name: 'pass',
                    allowBlank : false,
                    triggers: {
                        glyphed: {
                            cls: 'trigger-glyph-noop auth-password-trigger'
                        }
                    }
                },
                {
                    xtype: 'combobox',
                    name:'locale',
                    store: {
                        autoDestroy: true,
                        autoLoad: true,
                        fields: ['abbr', 'state'],
                        proxy: {
                            type: 'ajax',
                            url: '/cms/include/data_lang.php',
                            reader: {
                                type: 'json',								
                                rootProperty: 'rows'
                            }
                        }                   
                    },
                    valueField:'abbr',
                    displayField:'state',
                    queryMode: 'local',
                    triggerAction: 'all',
                    editable: false,
                    value: Config.locale                   
                },                
                {
                    xtype: 'container',
                    layout: 'hbox',
                    items: [
                        {
                            xtype: 'checkboxfield',
                            name: 'remember',
                            inputValue: '1',                          
                            flex : 1,
                            cls: 'form-panel-font-color rememberMeCheckbox',
                            height: 30,
                            boxLabel: _('Запомнить')
                        },
                        {
                            xtype: 'button',
                            cls: 'link-button',
                            text: 'Забыли пароль ?',
                            listeners: {
                                click: 'onResetButton'
                            }                             
                        }
                    ]
                },
                {
                    xtype: 'button',
                    scale: 'large',
                    //ui: 'soft-green',
                    iconAlign: 'right',
                    iconCls: 'x-fa fa-angle-right',
                    text: _('Войти'),
                    listeners: {
                        click: 'onLoginClick'
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