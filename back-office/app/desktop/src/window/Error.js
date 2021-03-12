Ext.define('Cetera.window.Error', {

    extend:'Ext.Window',    
      
    autoHeight: true,
    bodyBorder: false,
    buttonAlign: 'center',
    bodyStyle:'background: none',
    border: false,
    modal: true,
    width:500,
    closable: false,
    resizable: false,
    plain: true,
    padding: 10,
   
    initComponent : function(){
    
        this.title = Config.Lang.error;
        
        this.button = new Ext.Button({
            text: Config.Lang.more + ' >>',
            scope: this,
            hidden: !this.ext_msg,
            handler: function() {
                if (this.more.hidden) {
                    this.more.show();
                    this.button.setText('<< ' + Config.Lang.more);
                } else {
                    this.more.hide();
                    this.button.setText(Config.Lang.more + ' >>');
                }
            }
        });
        
        this.main = new Ext.Panel({
            html: this.msg,
            autoScroll: true,
            padding: '0 0 10 0',
            bodyStyle:'background: none',
            border: false
        });
        
        this.more = new Ext.Panel({
            hidden: true,
            html: this.ext_msg,
            autoScroll: true,
            padding: 10,
            height: 150
        });
    
        this.items = [
            this.main,
            this.button,
            this.more
        ];
        
        if (!this.nobuttons)
            this.buttons = [{
                text: Ext.MessageBox.buttonText.ok,
                scope: this,
                handler: function(){
                    this.hide();
                }
            }];
        
        this.callParent();
    }
});