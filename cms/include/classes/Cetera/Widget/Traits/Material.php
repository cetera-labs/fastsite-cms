<?php
namespace Cetera\Widget\Traits; 

trait Material {
	
	use Catalog;
	
	protected $_material = null;	
	
	public function getMaterial($useApp = true)
	{
		if (!$this->_material)
		{
			if ( $this->getParam('material') && $this->getParam('material') instanceof \Cetera\Material )
			{
				$this->_material = $this->getParam('material');
			}
			else try
			{
				$tid = $this->getParam('material_type');
				$mid = (int)$this->getParam('material_id');
								
				if ($tid)
				{ // указан ID типа
					if (!$mid) throw new \Exception('Material ID required');
					$this->_material = \Cetera\Material::getById( $mid, new \Cetera\ObjectDefinition($tid) );
				}
				else
				{
					$c = $this->getCatalog($useApp);
					if ($mid)
					{
						$this->_material = $c->getMaterialByID($mid);
					}
					else
					{
						$alias = $this->getParam('material_alias');
						if (!$alias)
						{
							if (!$useApp) throw new \Exception('Material alias required');
							$alias = current(explode('/', $this->application->getUnparsedUrl() ));
							if (!$alias) $alias = 'index';
						}
						$this->_material = $c->getMaterialByAlias($alias);
					}
				}
			}
			catch (\Exception $e)
			{
				$this->_material = null;
			}
		}
		return $this->_material;
	}

}