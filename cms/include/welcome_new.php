<?php
/**
 * Fastsite CMS 3 
 * 
 * Информационной модуль "О системе". Показывает информацию о системе, лицензию и т.д.  
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author unknown 
 **/
 
include('common_bo.php');

try {
    if (file_exists(WWWROOT.UPGRADE_SCRIPT)) unlink(WWWROOT.UPGRADE_SCRIPT);
    if (file_exists(WWWROOT.INSTALL_SCRIPT)) unlink(WWWROOT.INSTALL_SCRIPT);
} catch (\Exception $e) {}

$setup_theme = false;

if ($user->allowAdmin()) {
	
	if ($application->getVar('setup_theme') && \Cetera\Server::getDefault())
		    $setup_theme = \Cetera\Server::getDefault()->getTheme()->name;
    
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns:mpc>
<head>
    <style>
body {
    padding: 0px;
    background-color: window;
}

h1 {
    padding: 8px;
    font-size: 18px;
    color: inactivecaptiontext;
    font-family: Tahoma;
    margin: 0px;
}

h1 b {
    color: captiontext;
}

hr {
	color: buttonface;
	height: 1px;
}

p {
	margin-bottom: 10px;
	margin-top: 5px;
}

.content {
    padding: 10px;
}

.header {
    border-top: 1px solid window;
}

.about {
    width: 500px;
    font-weight: bold;
    font-size: 14px;
    line-height: 20px;
	padding-top: 20px;
}

.center {
    text-align: center;
}

.info {
	margin: 20px;
}

.info td {
	padding: 5px 5px 30px 5px;
}

.info td.icon {
	width: 20px;
}

.simple td {
	padding: 0;
}  

td {
    font-size: 12px;
}  

.new_version {
    padding: 10px;
    background: #ffefef;
    margin-top: 10px;
}

.latest {
    padding: 10px;
    background: #efffef;
    margin-top: 10px;
}

.big-button .x-btn-inner {
	font-size: 150%;
}

table.partner {
	margin: 0 auto;
}

table.partner td {
	font-size: 120%;
	padding: 1rem 2rem;
	margin: 10px;
	background: #efefef;
}
    </style>
	
<?php if ($setup_theme) : ?>
<script>
Ext.require('Cetera.model.Theme');

Ext.create('Ext.Button', {
	id: 'help-publish-btn',
	text: '<b><?=$translator->_('Как опубликовать материал?');?></b>',
    renderTo: 'publish_help',
	padding: '10 20',
	margin: 3,
	cls: 'big-button',
    handler: function() {
		
		Ext.create('Cetera.help.Publish');
	
	}
});

Ext.create('Ext.Button', {
	text: '<b><?=$translator->_('Настроить тему оформления');?></b>',
    renderTo: 'setup_theme',
	padding: '10 20',
	margin: 3,
	cls: 'big-button',
    handler: function() {

		Cetera.getApplication().loading.show();
		var Theme = Ext.ModelManager.getModel('Cetera.model.Theme');
		Theme.load('<?=$setup_theme;?>', {
			success: function(t) {
				Cetera.getApplication().loading.hide();
                Ext.create('Cetera.theme.Activate',{
                    theme: t
                });   
			},
			failure: function(t) {
				Cetera.getApplication().loading.hide();
			}
		});	
	
	}
});

Ext.create('Ext.Button', {
	text: '<?=$translator->_('Скрыть подсказки');?>',
    renderTo: 'setup_theme_cancel',
	margin: 3,
    handler: function() {
		
		Ext.Msg.show({
			title: '<?=$translator->_('Сообщение');?>',
			msg: '<?=$translator->_('Вы можете настроить тему в любое время в модуле "Темы"');?>',
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.INFO
		});		
		
        Ext.Ajax.request({
            url: '/cms/include/action_prefs.php',
            params: {
				name: 'setup_theme',
                value: '0'
            },
            success: function(resp){
				Ext.get('theme_setup').hide(true);
			}
        }); 		
		
	}
});
</script>
<?php endif; ?>    	
    
</head>
<body class="inset" scroll="no">
<table width="100%" height="100%" cellpadding="0" cellspacing="0">
    <tr height="100%">
        <td style="padding: 10px">
        
            <table width="100%" height="100%" cellpadding="0">
			
<?php if ($setup_theme) : ?>
			<tr height="0">
			<td>
				<div id="theme_setup" class="theme_setup">
						<h2><?=$translator->_('Поздравляем с успешной установкой Fastsite CMS');?></h2>
						<div id="setup_theme"></div>
						<div id="publish_help"></div>
						<div id="setup_theme_cancel"></div>
				</div>
			</td>
			</tr>
<?php endif; ?>  	
			
            <tr height="100%"><td align="center">
                <div class="about">
                    <img src="https://www.fastsite.ru/logo-fastsite.svg"/>
                    <br />
                    <?=$translator->_('Система контент-менеджмента веб-сайтов');?><br /><?=APP_NAME?> v<?=VERSION?>
                </div>
            </td></tr>
			
			<tr height="0">
				<td>
					<table class="partner">
						<tr>
							<td><a href="https://ceteralabs.com/partnership/partners/" target="_blank"><?=$translator->_('Наши партнеры');?></a></td>
							<td><a href="https://ceteralabs.com/partnership/" target="_blank"><?=$translator->_('Партнерская программа');?></a></td>
							<td><a href="https://ceteralabs.com/partnership/become/" target="_blank"><?=$translator->_('Стать партнером');?></a></td>
						</tr>
					</table>
				</td>
			</tr>				
            
            <tr height="0"><td>
                <hr noshade />
                <div align="left">
                &copy; 2000-<?=date('Y',time());?> <?=$translator->_('Компания')?> <a href="http://<?=APP_WWW?>" target="_blank">Cetera labs</a><br /><BR />
                <a href="https://github.com/cetera-labs/fastsite/blob/master/LICENSE">MIT License</a><br>

                </div>
            </td></tr>
            </table>
        
        </td>
    </tr>
</table>
</body>
</html>
