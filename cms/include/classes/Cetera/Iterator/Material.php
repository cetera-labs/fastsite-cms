<?php
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera\Iterator;
 
/**
 * Итератор материалов
 *
 * @package FastsiteCMS
 **/
class Material extends DynamicObject {
     
    /**
     * Раздел 
     *         
     * @var Catalog    
     */        
    protected $catalog = null;
	
    /**
     * Включать материалы из подразделов 
     *         
     * @var boolean    
     */   	
    protected $subfolders = false;	
	
    /**
     * Включать неопубликованные материалы
     *         
     * @var boolean    
     */   	
    protected $unpublished = false;		
    

    /**
     * Конструктор              
     *  
     * @param Object $object               
     * @return void  
     */ 
    public function __construct($object)
    {
        if ($object instanceof \Cetera\Section) {
        
            $this->catalog = $object; 
            $this->objectDefinition = $this->catalog->materialsObjectDefinition;
			parent::__construct($this->catalog->materialsObjectDefinition);
            
        } elseif ($object instanceof \Cetera\ObjectDefinition) {
        
			parent::__construct($object);
            
        } else {
        
            throw new \Cetera\Exception\CMS('В конструктор должен быть передан либо Section, либо ObjectDefinition');
            
        }
        
    } 
	
    /**
     * Включать материалы из подразделов 
     *         
     * @return Cetera\Iterator\Material  
     */	
    public function subFolders($subfolders = true)
    {
         $this->subfolders = $subfolders;         
         return $this;
    }  

    /**
     * Включать неопубликованные материалы
     *         
     * @return Cetera\Iterator\Material  
     */	
    public function unpublished($unpublished = true)
    {
         $this->unpublished = $unpublished;         
         return $this;
    }	
     
    /**    
     * @ignore
     */		 
    protected function fixQuery($query)
    {
		$query->addSelect('main.idcat', 'main.alias');
		parent::fixQuery($query);
	}
	
	protected function fixWhere($query)
	{
        if ($this->catalog) {
            if ($this->subfolders)
                $query->andWhere('main.idcat IN (:idcat)')->setParameter('idcat', $this->catalog->getSubs(), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);
                else $query->andWhere('main.idcat=:idcat')->setParameter('idcat', $this->catalog->prototype->id);
        }
        
        $app = \Cetera\Application::getInstance();
        if (!$app->previewMode() && !$this->unpublished) $query->andWhere('main.type&'.MATH_PUBLISHED.'='.MATH_PUBLISHED.' and (main.dat<=NOW() or main.dat IS NULL or main.type&'.MATH_SHOW_FUTURE.'='.MATH_SHOW_FUTURE.')');      
    }
    
}
