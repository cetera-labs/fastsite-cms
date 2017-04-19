<?
include_once('common_bo.php');
$funcNum = $_GET['CKEditorFuncNum'];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>    
    <meta charset="utf-8">
    <base href="../">
    <?\Cetera\Util::commonHead()?>
    <script type="text/javascript" src="config.php"></script>
<script>

Ext.Loader.setPath('Ext.ux', '/<?=LIBRARY_PATH?>/extjs4/ux');
Ext.Loader.setPath('Cetera', 'app');

Ext.require([
    'Ext.ux.StatusBar'
]);


Ext.onReady(function(){

    Config.setLocale(Config.locale);
	
    Ext.QuickTips.init();     
   
    var selectedHandler = function() {
        if (!filePanel.url) return;
        window.opener.CKEDITOR.tools.callFunction(<?=$funcNum?>, filePanel.url);
        window.close();
    }
   
    var filePanel = Ext.create('Cetera.fileselect.Panel', {
        activePanel:1,
        defaultExpand: 'images',
        buttons: [
            {text: 'Ok', handler: selectedHandler},
            {text: 'Отмена', handler: function() {window.close();}}
        ]
    });
   
    var viewport = Ext.create('Ext.Viewport', {
        layout: 'fit',
        items: filePanel
    });
        
    filePanel.files.on('dblclick', selectedHandler);
    filePanel.files2.on('dblclick', selectedHandler);
    
});

</script>
</head>
<body></body>
</html>
