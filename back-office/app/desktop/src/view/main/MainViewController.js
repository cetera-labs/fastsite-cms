Ext.define('Cetera.view.main.MainViewController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.mainviewcontroller',   


    onNavigationTreeSelectionChange: function(  treelist, record, eOpts ) {
        
        Cetera.getApplication().redirectTo(record.getId());
        var westPanel = Ext.getCmp('west-panel');
        if (westPanel.floatable) {
            westPanel.close();
        }        
        
    }
});