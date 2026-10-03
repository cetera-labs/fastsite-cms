<?php
ob_start();
include_once('include/common.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo APP_NAME; ?></title>
    
    <link rel="stylesheet" type="text/css" href="/cms/css/global.css">
    <script type="text/javascript" src="/cms/js/vendor.js"></script>
    
    <?php if ($application->getVar('setup_done')) : ?>    
    
        <script type="text/javascript" src="/cms/config.php"></script>	
        <script type="text/javascript" src="/cms/js/app.js"></script>
    <?php else : ?> 
        <?php include('setup.php'); ?>         
    <?php endif ?>     

     
</head>
<body id="main_body" class="body-backoffice">
</body>
</html>
