<?php
namespace Cetera\Widget\Traits; 

trait Catalog {
	
	protected $_cat = false;
	
    public function getCatalog($useApp = true)
    {
        if (!$this->_cat)
		{
			
            $c = $this->getParam('catalog');
						
			if ($c instanceof \Cetera\Catalog) {
				$this->_cat = $c;
			} 
			elseif ($c) {
				if (intval($c)) {
					$this->_cat = \Cetera\Catalog::getById($c);
				}
				else {
					$this->_cat = $this->application->getServer()->getChildByPath($c);
				}
			}
			
			if (!$this->_cat && $useApp) {
				$this->_cat = $this->application->getCatalog();
			}
                    
        }
        return $this->_cat;
    }

}