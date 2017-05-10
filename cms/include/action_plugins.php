<?php
namespace Cetera;

/**
 * Cetera CMS 3
 *
 * Действия с пользователями
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru)
 * @author Roman Romanov <nicodim@mail.ru>
 **/

include('common_bo.php');

if (!$user->allowAdmin()) throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$res = array(
    'success' => false,
    'errors' => array()
);

$action = $_REQUEST['action'];
$plugin = $_REQUEST['plugin'];

if ($action == 'delete_data')
{
    Plugin::find($plugin)->delete(true);
} 
elseif ($action == 'delete')
{
    Plugin::find($plugin)->delete();
}
elseif ($action == 'enable')
{
    Plugin::find($plugin)->enable();
}
elseif ($action == 'disable')
{
    Plugin::find($plugin)->disable();
}
elseif ($action == 'install')
{

	$plugin = $_REQUEST['plugin'];
    ob_start();
	
    try {
            
        Plugin::install($plugin, function($text, $start, $br = false) { 
            if ($start) echo '<b>';
            echo $text; 
            if ($start)
			{
				echo '</b>';
				if ($br) echo '<br>'; else echo ' ... ';
			}
			else
			{
				echo '<br>';
			}
        }, $translator);      
    
    } catch (\Exception $e) {
    
        header("HTTP/1.0 201");
        echo '<span class="error">Ошибка!<span class="error-desc">'.$e->getMessage().'</span></span>';
    
    }  

    ob_end_flush();
    die();

}

echo json_encode($res);