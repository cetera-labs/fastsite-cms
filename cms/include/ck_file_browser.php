<?php include_once('common_bo.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>    
    <meta charset="utf-8">
    <base href="../">
    <?php \Cetera\Util::commonHead(); ?>
	<link rel="stylesheet" type="text/css" href="/<?php echo LIBRARY_PATH; ?>/cropper/cropper.min.css">
	<script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/cropper/cropper.min.js"></script>
    <script type="text/javascript" src="config.php"></script>
<script>

Ext.Loader.setPath('Ext.ux', '/<?php echoLIBRARY_PATH?>/extjs4/ux');
Ext.Loader.setPath('Cetera', 'app');

Ext.require([
    'Ext.ux.StatusBar'
]);


Ext.onReady(function(){

    Config.setLocale(Config.locale);
	
    Ext.QuickTips.init();     
   
    var selectedHandler = function(url) {
        if (!url) return;
        window.opener.CKEDITOR.tools.callFunction(<?php echo $_GET['CKEditorFuncNum']?>, url);
        window.close();
    }
   
    var filePanel = Ext.create('Cetera.fileselect.Panel', {
        activePanel:1,
        defaultExpand: 'images'
    });
   
    var viewport = Ext.create('Ext.Viewport', {
        layout: 'fit',
        items: filePanel
    });
        
    filePanel.on('cancel', function() {window.close();} );
	filePanel.on('select', selectedHandler );
    
});

</script>
</head>
<body></body>
</html>
