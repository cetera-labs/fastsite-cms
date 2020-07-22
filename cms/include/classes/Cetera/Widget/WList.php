<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera\Widget; 

/**
 * Виджет "Список материалов"
 * 
 * @package CeteraCMS
 */ 
class WList extends Templateable { 

	use Traits\Catalog;
	use Traits\Paginator;
	
	public static $name = 'List';
	
	protected $_children = null;
 	
	protected function initParams()
	{
		$this->_params = [
			'name'               => '',
			'catalog'            => 0,
			'where'              => null,
			'limit'              => 10,
			'page'               => null,
			'page_param'         => 'page',
			'order'              => 'dat',
			'sort'               => 'DESC',
			'catalog_link'       => null,
			'iterator'           => null,
			'paginator'          => false,
			'paginator_url'      => '?{query_string}',
			'paginator_template' => false,
			'ajax'               => false,
			'infinite'           => false,
			'subsections'		 => false,
			'css_class'          => 'content',
			'css_row'            => '',
			'template'		     => 'default.twig',
			'filter'			 => null,
            'not_found_block'    => false,
            'not_found_text'     => '<div class="callout primary">Материалы не найдены</div>',
            'materials'          => null,
		]; 
		
	}

	protected function init()
	{
		if ($this->_params['ajax'] && $this->_params['infinite']) {
			$this->_params['paginator'] = true;
			if (!$this->_params['paginator_template']) {
				$this->_params['paginator_template'] = 'infinite.twig';
			}
		}
		$this->setParam('catalog', $this->getCatalog()->id);
	}
    
	/**
	 * Список материалов для показа
	 */ 	
    public function getChildren()
    {
        
		if (!$this->_children) {
            if ($this->getParam('materials')) {
                $m = $this->getParam('materials');
                if (!is_array($m)) {
                    $m = json_decode($m, true);
                }
                if (!is_array($m)) {
                    $m = explode(';',$m);
                }
                if (is_array($m)) {
                    $children = [];
                    foreach ($m as $id) {
                        try {
                            if (is_numeric($id)) {
                                $children[] = $this->getCatalog()->getMaterialById($id);
                            }
                            else {
                                list($tid,$mid) = explode('_',$id);
                                $children[] =  \Cetera\DynamicFieldsObject::getByIdType($mid, $tid);
                            }
                        }
                        catch (\Exception $e) {
                            continue;
                        }
                    }
                    if (count($children)) {
                        $this->_children = $children;
                    }
                }
            }
        }
        if (!$this->_children) {
			if ($this->getParam('iterator')) {
				$this->_children = $this->getParam('iterator');
			}
			else {
				try {
					$this->_children = $this->getCatalog()->getMaterials();
                    if ($this->getParam('order')) {
                        $this->_children->orderBy($this->getParam('order'), $this->getParam('sort'));
                    }
                    $this->_children->orderBy('id', 'ASC', true);
					if ($this->getParam('subfolders') || $this->getParam('subsections')) {
						$this->_children->subfolders();
					}
					if ($this->getParam('filter')) {
						list($filter) = explode(';',$this->getParam('filter'));
						eval('$this->_children->'.$filter.';');
					}
				}
				catch (\Exception $e) {
					return [];
				}					
			}
			if ($this->getParam('limit')) $this->_children->setItemCountPerPage($this->getParam('limit')); 
			if ($this->getParam('where')) $this->_children->where($this->getParam('where')); 
			$this->_children->setCurrentPageNumber( $this->getPage() ); 			
		}
		return $this->_children;
    } 

    protected function _getHtml()
    {
        if (!$this->getParam('not_found_block') || $this->getChildren()->count() > 0) {
            return parent::_getHtml();
        }
        else {
            return $this->getParam('not_found_text');
        }
        
    }
}