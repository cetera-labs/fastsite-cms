Ext.define('Cetera.login.Login', {
    extend: 'Ext.Window',
    
    title: Config.appName + ' v' + Config.appVersion + ' - Login',
    width: 500,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    plain:true,
    buttonAlign:'center',
    autoShow: true,
    closable: false,
    resizable: false,
    
    initComponent : function(config) {
            
        this.form = Ext.create('Ext.form.FormPanel', {
			flex: 1,
            baseCls: 'x-plain',
            defaultType: 'textfield',
            method: 'POST',
            padding: 10,
            url: 'include/action_login.php',
            defaults   : { 
                anchor: '0', 
                hideEmptyLabel: false
            },
            fieldDefaults : { 
                labelWidth: 105
            },
            waitMsgTarget: true,
            
            items: [
	
                Ext.create('Ext.form.field.Trigger',{
                    fieldLabel: Config.Lang.username,
                    allowBlank: false,
                    triggerCls: 'icon-help',
                    onTriggerClick: function() {
                        Ext.Msg.confirm(
                            Config.Lang.forgetPassword,
                            Config.Lang.recoverPassword,
                            function(btn) {
                                if (btn == 'yes') {
                                    if (!this.getValue()) {
                                         setTimeout(function(){
                                            Ext.Msg.alert(Config.Lang.forgetPassword, Config.Lang.needUsername);
                                         }, 10);
                                    } else {
                                          Ext.Ajax.request({
                                              url: 'include/action_login.php',
                                              params: {
                                                  recover: this.getValue()
                                              },
                                              success: function(response){
                                                  var text = response.responseText;
                                                  Ext.Msg.alert(Config.Lang.forgetPassword, text);
                                              }
                                          });                    
                                    }              
                                }
                            },
                            this
                        );              
                    },
                    name: 'login' 
                }),				
                {
                    fieldLabel: Config.Lang.password,
                    name: 'pass',
                    allowBlank: false,
                    inputType: 'password'
                },
	
                Ext.create('Ext.form.ComboBox', {
                    fieldLabel: Config.Lang.lang,
                    name:'locale',
                    store: Ext.create('Ext.data.JsonStore',{
                        autoDestroy: true,
                        autoLoad: true,
                        fields: ['abbr', 'state'],
                        proxy: {
                            type: 'ajax',
                            url: 'include/data_lang.php',
                            reader: {
                                type: 'json',								
								root: 'rows'
                            }
                        }                   
                    }),
                    valueField:'abbr',
                    displayField:'state',
                    queryMode: 'local',
                    triggerAction: 'all',
                    editable: false,
                    value: Config.locale
                }),
				
				{
                    xtype: 'checkbox',
                    boxLabel: Config.Lang.remember,
                    name: 'remember',
                    inputValue: '1'
                }				
            ]
        });
    
        Ext.apply(this, {

            items: [
                Ext.create('Ext.Panel',{
                    height: 63,
                    border: false,
                    html: '<div style="text-align: center; padding-top: 10px"><img src="images/logo-fastsite.svg" width="323" height="40" /></div>',
                    bodyStyle: 'border-bottom: 3px solid #b13330'
                }),
                this.form,
                Ext.create('Ext.Panel',{
                    height: 50,
                    border: false,
                    html: '<div id="uLogin" style="text-align: center; padding-top: 10px" data-ulogin="display=panel;fields=nickname,email,first_name,last_name;providers=vkontakte,facebook,google;hidden=;redirect_uri=http%3A%2F%2F' + Config.serverName + Config.cmsPath + 'index.php"></div><p style="color:red; text-align: center; margin: 0">' + userMessage + '</p>',
                    bodyStyle: 'background: none;'
                }),                
            ],

            buttons: [{
                text:    Config.Lang.login,
                scope:   this,
                handler: this.submitForm 
            }]
    
        });
        
        Ext.create('Ext.util.KeyMap', { 
            target: document,               
            key: 13, // this works,
            scope: this,
            fn: this.submitForm
        });       

        this.callParent(arguments);
    },
    
    submitForm: function() {
    
        var f = this.form.getForm();
    
        Config.setLocale(f.getValues().locale);
    
        f.submit({
            scope: this,
            waitMsg: Config.Lang.wait,
            success: function(form, action) {
                this.fireEvent('login', action.result);
            }
        });        
    }
}); 
