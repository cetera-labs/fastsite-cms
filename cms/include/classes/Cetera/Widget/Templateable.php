<?php
namespace Cetera\Widget; 

class Templateable extends Widget {

    private $_templateFile = FALSE;
    
    protected function getTemplateFile()
    {
        if ($this->_templateFile === FALSE)
		{

            $this->_templateFile = 'default.twig';
            		
			if ($this->getParam('template')) $this->_templateFile = $this->getParam('template');
			if (!substr_count($this->_templateFile,'.')) $this->_templateFile .= '.twig'; 			
		
        }
        return $this->_templateFile;
    }
    
    protected function _getHtml()
    {

        if ($tpl = $this->getTemplateFile())
		{
			
			try
			{
                
				$path_parts = pathinfo($tpl);
			
				if (( $path_parts['extension']=='smarty' || $path_parts['extension'] == 'tpl' ) && is_object($GLOBALS['smarty']) && $GLOBALS['smarty']->smarty) {
				
					$GLOBALS['smarty']->assign('widget', $this);
					return $GLOBALS['smarty']->fetch($tpl);
				
				} elseif ($path_parts['extension'] == 'twig') {
			
					$twig = $this->application->getTwig();
					
					if (substr_count( WWWROOT.ltrim($tpl,'/'), $this->application->getTemplateDir() ) > 0)
					{
						$tpl = str_replace($this->application->getTemplateDir().'/design', '', WWWROOT.ltrim($tpl,'/'));					
					}
					else 
					{
						$tpl = '@widget/'.strtolower($this->widgetName).'/'.$tpl;
					}
					
					return $twig->render( $tpl , array(
						'widget' => $this 
					));   
				
				} else {
					
					ob_start();
					
					if (file_exists(WWWROOT.$tpl) )
					{
						include( WWWROOT.$tpl );
					}
					else 
					{
						$tpl = strtolower($this->widgetName).'/'.$tpl;
						if (file_exists($this->application->getTemplateDir().'/widgets/'.$tpl) )
						{
							include($this->application->getTemplateDir().'/widgets/'.$tpl);
						} 
						elseif (file_exists( CMSROOT.'/widgets/'.$tpl ) ) 
						{			
							include( CMSROOT.'/widgets/'.$tpl );
						}
					}
				  
					$result = ob_get_contents();
					ob_end_clean();
					return $result;
				
				}
			
			}
			catch (\Exception $e)
			{
				return $e->getMessage();
			}

        } 
		else
		{
        
            return parent::_getHtml();
            
        }
    
    }
	
	public static final function getTemplates()
	{
		$data = array();
		
		$widgets_path = '/widgets/'.strtolower(static::getName());
		
		$path = static::getData('path')?ltrim(static::getData('path'),'/'):CMS_DIR;
		$path .= $widgets_path;
		if (static::getName() && file_exists(WWWROOT.$path) && is_dir(WWWROOT.$path))
		{
			$iterator = new \DirectoryIterator(WWWROOT.$path);
			foreach ($iterator as $fileinfo)
			{
				if (!$fileinfo->isFile()) continue;	
				$fn = $fileinfo->getFilename();
				$data[] = array(
					'name'     => $fn,
					'writable' => false,
					'path'     => '/'.$path.'/'.$fn,
					'folder'   => $widgets_path,
				);					
			}
		}
		
		foreach (\Cetera\Theme::enum() as $theme)
		{
			$path = THEME_DIR.'/'.$theme->name.$widgets_path;
            if (file_exists(DOCROOT.$path) && is_dir(DOCROOT.$path))
			{
				$iterator = new \DirectoryIterator(DOCROOT.$path);
				foreach ($iterator as $fileinfo)
				{
					if (!$fileinfo->isFile()) continue;	
					$fn = $fileinfo->getFilename();
					$data[] = array(
						'name'     => $fn,
						'theme'    => $theme->name,
						'folder'   => $widgets_path,
						'writable' => true,
						'path'     => '/'.$path.'/'.$fn,
					);					
				}				
            }   			
		
		}				
		
		return $data;
	}
}