<?php
ob_start();
include('include/common.php');

if (isset($_POST['token'])) {
    $_s = \Cetera\Util::curlGet('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
    $u = json_decode($_s, true);
    if ($u && $u['uid']) {
    
        $application->connectDb();
        $application->initSession();
        $application->getAuth()->authenticate(new Cetera\UserAuthAdapterULogin($u, false));
        $user = $application->getUser();        
        
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo APP_NAME; ?></title>
    
<?php if (COMPOSER_INSTALL) : ?>  

    <link rel="stylesheet" type="text/css" href="/cms/css/global.css">
    <script type="text/javascript" src="/cms/js/vendor.js"></script>
    
    <?php if ($application->getVar('setup_done')) : ?>    
    
        <script type="text/javascript" src="/cms/config.php"></script>	
        <script type="text/javascript" src="/cms/js/app.js"></script>
        <script src="//ulogin.ru/js/ulogin.js"></script>
        
        <script type="text/javascript">	
            <?php if ($user && !$user->allowBackOffice()) : ?>var userMessage = '<?=$application->getTranslator()->_('Недостаточно полномочий')?>';<?php else : ?> var userMessage = '';<?php endif ?> 		
        </script>  
    
    <?php else : ?> 
        <?php include('setup.php'); ?>         
    <?php endif ?>     

<?php else : ?>  
    
    <link rel="stylesheet" type="text/css" href="css/main.css">
	<link rel="stylesheet" type="text/css" href="/<?php echo LIBRARY_PATH; ?>/cropper/cropper.min.css">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	<?php if (isset($_GET['ext6'])) : ?>
	
    <link rel="stylesheet" type="text/css" href="/<?php echo LIBRARY_PATH; ?>/extjs6/classic/theme-triton/resources/theme-triton-all.css"> 
	<script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/extjs6/ext-all-debug.js"></script>
    <script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/extjs6/classic/theme-triton/theme-triton.js"></script>	
    <script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/extjs6/packages/ux/classic/ux.js"></script>		
	
	<?php elseif (isset($_GET['ext5'])) : ?>
	
	<script type="text/javascript">
	var Ext = Ext || {};
	Ext.manifest = {
		compatibility: {
			ext: '4.2'
		}
	}
	</script>
    <link rel="stylesheet" type="text/css" href="/<?php echo LIBRARY_PATH; ?>/extjs5/packages/ext-theme-classic/build/resources/ext-theme-classic-all.css"> 
    <script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/extjs5/ext-all-debug.js"></script>	
    <script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/extjs5/packages/ext-theme-classic/build/ext-theme-classic.js"></script>	
	
	<?php else : ?>
    
	<link rel="stylesheet" type="text/css" href="/<?php echo LIBRARY_PATH; ?>/extjs4/resources/css/ext-all.css"> 
    <script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/extjs4/ext-all.js"></script>
	
	<?php endif; ?>	
	
	<script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/beautify/beautify-css.js"></script>
	<script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/beautify/beautify-html.js"></script>
	<script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/beautify/beautify.js"></script>
	<script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/minify/htmlminifier.min.js"></script>
	<script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/ace/ace.js"></script>
	<script type="text/javascript" src="/<?php echo LIBRARY_PATH; ?>/cropper/cropper.min.js"></script>
    <script src="//ulogin.ru/js/ulogin.js"></script>
    
    <?php if ($application->getVar('setup_done')) : ?>    
    
    <script type="text/javascript" src="/cms/config.php"></script>	
	<script type="text/javascript" src="/cms/app.js"></script>
	
    <script type="text/javascript">
	
    <?php if ($user && !$user->allowBackOffice()) : ?>
    var userMessage = '<?=$application->getTranslator()->_('Недостаточно полномочий')?>';
    <?php else : ?> 
    var userMessage = '';
    <?php endif ?> 		
    </script>  
    
    <?php else : ?> 

    <link rel="stylesheet" type="text/css" href="/cms/css/setup.css">
    <?php include('setup.php'); ?>     
    
    <?php endif ?> 
<?php endif; ?>    
     
</head>
<body id="main_body" class="body-backoffice">
</body>
</html>
