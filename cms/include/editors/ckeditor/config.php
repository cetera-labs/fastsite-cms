<?php 
header('Content-Type: application/javascript; charset=UTF-8');
include('../../common_bo.php');
?>

(function() {

    CKEDITOR.plugins.addExternal('widgets', '/<?=CMS_DIR?>/include/editors/ckeditor/plugins/widgets/','plugin.js');
    CKEDITOR.plugins.addExternal('youtube', '/<?=CMS_DIR?>/include/editors/ckeditor/plugins/youtube/','plugin.js');
	
	CKEDITOR.dtd.cms = { em:1 };
	CKEDITOR.dtd.$block.cms = 1;
	CKEDITOR.dtd.body.cms = 1;
    
})();


CKEDITOR.editorConfig = function( config )
{
                
    config.toolbarGroups = [
        { name: 'mode' },
        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
        { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
        { name: 'links' },
        { name: 'insert' },
        { name: 'tools' },
        { name: 'others' },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
        { name: 'styles' },
        { name: 'colors' },
        { name: 'cetera' },
        { name: 'about' }
    ];
	         
    config.baseFloatZIndex = 20000;       
    config.extraPlugins = 'youtube,font,showblocks,widgets,colorbutton,colordialog';
    config.removePlugins = 'iframe';
    config.allowedContent = true;
    
    config.pasteFromWordPromptCleanup = true;
    config.pasteFromWordRemoveStyles = true;
    config.pasteFromWordRemoveFontStyles = true;
    config.pasteFromWord_heuristicsEdgeList = true;
    
    config.resize_enabled = false;
    config.language = '<?=$application->getLocale()?>';
    config.filebrowserBrowseUrl = '/<?=CMS_DIR?>/include/ck_file_browser.php';
    config.filebrowserUploadUrl = '/<?=CMS_DIR?>/include/ck_file_uploader.php?path=<?php echo USER_UPLOAD_PATH.date('Ymd') ?>/';
    config.filebrowserUploadMethod = 'form';
    <?php if ($application->getVar('htmleditor.css')) : ?>
    config.contentsCss = '<?php echo $application->getVar('htmleditor.css') ?>?' + ( new Date() * 1 );
    <?php endif; ?>
    <?php if ($application->getVar('htmleditor.body_class')) : ?>
    config.bodyClass = '<?php echo $application->getVar('htmleditor.body_class'); ?>';
    <?php endif; ?>
    <?php if ($application->getVar('htmleditor.styles')) : ?>
    config.stylesSet = '<?php echo $application->getVar('htmleditor.styles'); ?>';
    <?php endif; ?>
	
    <?php 
	if ($application->getVar('ckeditor.config') && file_exists(WWWROOT.$application->getVar('ckeditor.config')))
	{
		include( WWWROOT.$application->getVar('ckeditor.config') );
	}
	?>    
};
