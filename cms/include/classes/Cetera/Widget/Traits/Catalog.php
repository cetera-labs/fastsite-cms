<?php
namespace Cetera\Widget\Traits; 

trait Catalog {
	
	protected $_cat = false;
	
    public function getCatalog($useApp = true)
    {
        if (!$this->_cat)
		{
			
            $c = $this->getParam('catalog');
			
			if ($c instanceof \Cetera\Catalog)
			{
				$this->_cat = $c;
			} 
			elseif ($c)
			{
				$this->_cat = \Cetera\Catalog::getById($c);
			}
			
			if (!$this->_cat && $useApp)
			{
				$this->_cat = $this->application->getCatalog();
			}
                    
        }
        return $this->_cat;
    }

}