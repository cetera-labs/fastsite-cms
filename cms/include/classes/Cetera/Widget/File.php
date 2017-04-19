<?php
namespace Cetera\Widget; 

/**
 * Виджет "Файл"
 *  
 * @package CeteraCMS
 */ 
class File extends Widget {

    protected function _getHtml()
    { 	
        $file = $this->getParam('file');
        if (!$file || !file_exists( DOCROOT . $file)) return '';
        
        if (substr($file,-4) == '.tpl' && is_object($GLOBALS['smarty']) && $GLOBALS['smarty']->smarty) {
      
            $GLOBALS['smarty']->assign('widget', $this);
            return $GLOBALS['smarty']->fetch(DOCROOT . $file);
        
        } elseif (substr($file,-4) == '.php') {
            
            ob_start();
            include(DOCROOT . $file);
            $result = ob_get_contents();
            ob_end_clean();
            return $result;
        
        } elseif (substr($file,-4) == '.jpg' || substr($file,-4) == '.gif' || substr($file,-4) == '.png') {
        
            return '<img src="'.$file.'" />';
        
		} elseif (substr($file,-5) == '.twig') {
							
				$path_parts = pathinfo(DOCROOT . $file);
                $twig = new \Twig_Environment(
                    new \Twig_Loader_Filesystem( $path_parts['dirname'] ),
                    array(
                        'cache'            => CACHE_DIR.'/twig',
                        'auto_reload'      => true,
                        'strict_variables' => true,
                    )
                ); 	

                return $twig->render( $path_parts['basename'] , array(
                    'widget' => $this 
                ));												
		
        } else {
		
            $contents = file_get_contents(DOCROOT . $file);
            return $contents;
        
        }       
    }

}