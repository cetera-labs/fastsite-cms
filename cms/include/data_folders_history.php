<?php
namespace Cetera;
/**
 * Cetera CMS 3
 * 
 * Список файлов   
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
include('common_bo.php');

$data = array();

$s = $application->getSession();

if (isset($s->explorer_history) && is_array($s->explorer_history)) {
    $a = $s->explorer_history;
    array_shift($a);
    foreach ($a as $path) 
        $data[] = array(
            'path' => rtrim(str_replace('|','/',$path),'/')
        );
}

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));
?>
