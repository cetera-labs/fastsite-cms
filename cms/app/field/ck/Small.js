Ext.define('Cetera.field.ck.Small', {

    extend:'Cetera.field.ck.Base',
    height: 300,
    editorConfig: {
        removePlugins : 'smiley,showblocks,find,flash,iframe,specialchar,wsc,scayt',
        removeButtons : 'Styles'
    }
       
});