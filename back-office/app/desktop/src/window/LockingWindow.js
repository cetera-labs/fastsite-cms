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
    
});
