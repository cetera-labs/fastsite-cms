Ext.define('Cetera.panel.MaterialEdit', {
    extend: 'Ext.FormPanel',

    layout: 'fit',
    bodyStyle: 'background: none',
    border   : false,
    pollForChanges: true,    
    timeout : 100,    
    
    fieldDefaults: {
        labelAlign: 'right',
        labelWidth: 180
    },   
    
    objectId: 0,
    objectDefinitionId: 0,
    
    initComponent : function() {
        
        this.task = Ext.TaskManager.start({
             run: function() {
                if (this.objectId > 0) {
                    Ext.Ajax.request({
                        url: '/cms/include/action_materials.php?action=lock&mat_id=' + this.objectId + '&type=' + this.objectDefinitionId,
                        failure: function(){},
                        scope: this
                    });
                }
             },
             scope: this,
             interval: 10000
        }); 

        
    },
    
});