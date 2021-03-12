// Панелька виджета
Ext.require('Cetera.field.FileEditable');

Ext.define('Cetera.widget.File', {
    extend: 'Cetera.widget.Widget',
        
    formfields : [{
        name: 'file',
        xtype: 'fileselect_editable',
        fieldLabel: Config.Lang.file,
        allowBlank: true
    }]

});