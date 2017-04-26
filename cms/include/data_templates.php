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
       //TEMPLATES_DIR
include('common_bo.php');

$data = array();
 
$r = fssql_query('SELECT * FROM widgets WHERE widgetName="Container" ORDER BY widgetAlias');

while ($f = mysql_fetch_assoc($r)) {
    $data[] = array(
        'name'     => $f['widgetAlias'].'.widget'
    );
}

if (isset($_REQUEST['templateDir'])) {

    $dir = DOCROOT.$_REQUEST['templateDir'];
    
} 
else {
    $c = Catalog::getById($_REQUEST['catalog_id']); 
	$application->setServer($c);	
	$dir = $application->getTemplateDir();
}

if (file_exists($dir) && is_dir($dir)) {

    $iterator = new \DirectoryIterator($dir);
    
    foreach ($iterator as $fileinfo) {
        if (!$fileinfo->isFile()) continue;
        
        $path_parts = pathinfo($fileinfo->getPathname());
        if ($path_parts['extension'] != 'php') continue;  
        if ($fileinfo->getFilename() == BOOTSTRAP_SCRIPT) continue;    
        
        $data[] = array(
            'name'     => $fileinfo->getFilename()
        );
    
    }
	
	$dir .= '/'.TWIG_TEMPLATES_PATH;
	
	if (file_exists($dir) && is_dir($dir)) {

		$iterator = new \DirectoryIterator($dir);
		
		foreach ($iterator as $fileinfo) {
			if (!$fileinfo->isFile()) continue;
			
			$path_parts = pathinfo($fileinfo->getPathname());
			if ($path_parts['extension'] != 'twig') continue;  
			if (substr($fileinfo->getFilename(),0,5) != 'page_') continue;    
			
			$data[] = array(
				'name'     => $fileinfo->getFilename()
			);
		
		}

	}		

}

function cmp($a, $b)
{
    $_a = strtolower($a['name']);
    $_b = strtolower($b['name']);
    if ($_a == $_b) return 0;
    return ($_a < $_b) ? -1 : 1;
}

//usort($data, "cmp");

echo json_encode(array(
    'success' => true,
    'rows'    => $data
));
?>
