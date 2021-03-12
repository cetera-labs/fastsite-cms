Ext.define('Cetera.window.MaterialEdit', {

    extend:'Ext.Window',

    maximizable: true, 
    closable:    true,
    resizable:   true,
    plain:       true,
    modal:       true,
    minHeight:   450,
    minWidth:    450,
    layout:     'fit',
    title:      Config.Lang.material,
    width:      Config.maxWindowWidth,
    height:     Config.maxWindowHeight,
	constrainHeader: true,
    
    materialForm: false,
    
});