Ext.define('Cetera.window.LockingWindowController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.lockingwindowcontroller',

    onLoginClick: function(btn) {
        var f = btn.up('form').getForm();
    
        Config.setLocale(f.getValues().locale);
        if (f.isValid()) {
            f.submit({
                url: '/cms/include/action_login.php',
                scope: this,
                waitMsg: _('Подождите...'),
                success: function(form, action) {
                    btn.up('window').close();
                    Ext.Loader.loadScript({
                        url: '/cms/config.php',
                        scope: this,
                        onLoad: function() { 
                            Cetera.getApplication().launchBackOffice();                            
                        }
                    });                    
                }
            }); 
        }
    },

    onResetClick:  function(btn) {
        var f = btn.up('form').getForm();
    
        if (f.isValid()) {
            f.submit({
                url: '/cms/include/action_login.php',
                scope: this,
                waitMsg: _('Подождите...'),
                success: function(form, action) {
                    if (action.result.message) {
                        Ext.Msg.alert(_('Результат'), action.result.message);
                        f.setValues({
                            recover: ''
                        });
                    }                    
                }
            }); 
        }        
    },
    
    onLoginButton: function(btn) {
        btn.up('window').close();
        Ext.create('Cetera.window.Login');
    },

    onResetButton:  function(btn) {
       btn.up('window').close();
       Ext.create('Cetera.window.PasswordReset');
    }    
});