/**
 * This class provides the modal Ext.Window support for all Authentication forms.
 * It's layout is structured to center any Authentication dialog within it's center,
 * and provides a backGround image during such operations.
 */
Ext.define('Cetera.window.LockingWindow', {
    extend: 'Ext.window.Window',
    xtype: 'lockingwindow',
    controller: 'lockingwindowcontroller',

    requires: [
        'Ext.layout.container.VBox',
        'Ext.Responsive'       
    ],
    
    html: '<div class="auth-locked-window-wrapper"></div>',    

    cls: 'auth-locked-window',
    closable: false,
    resizable: false,
    autoShow: true,
    titleAlign: 'center',
    maximized: true,
    //modal: true,
    scrollable: true,

    layout: {
        type: 'vbox',
        align: 'center',
        pack: 'center'
    },
    
    afterRender: function() {
        var wrapper = this.getEl();//.down('.auth-locked-window-wrapper');
        
        wrapper.on('mousemove', function(e,t){
            
            var elm = Ext.get(e.currentTarget.id);
            var wrapper = elm.down('.auth-locked-window-wrapper');
            
            var x = parseInt(20*e.pageX/elm.getWidth())-10;
            var y = parseInt(20*e.pageY/elm.getHeight())-10;
            
            wrapper.setStyle({
                left:  (-20 + x)+'px',
                right: (-20 - x)+'px',
                top:  (-20 + y)+'px',
                bottom: (-20 - y)+'px',                
            });            
                        
            
        });
        
        this.callParent();
    }
    
});
