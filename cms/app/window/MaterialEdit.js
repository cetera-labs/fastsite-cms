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
    
    html: '<table class="loading" style="position:absolute; top: 0; left: 0;" height="100%" width="100%"><tr><td style="vertical-align: middle"><div class="outer-circle small"></div><div class="inner-circle small"></div></td></tr></table>'
});