<?php
namespace Cetera; 

/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
class ObjectRenderer { 

	use DbConnection;   

    private $objectDefinition = false;
    private $objectId = false;
    private $catalog = false;
    private $pageHeight = false;
    public  $fields_def = false; 
    
    private $pages = array(); 
    private $currentPage = -1;
    
    private $pageTitle = false;
    
    private $object = null;
	
	private $initData = '';

    public function __construct($objectDefinition, $catalog = false, $objectId = false, $pageHeight = -1, $pageTitle = 'Properties') 
    {
        $this->setObjectDefinition($objectDefinition);
        $this->setCatalog($catalog);
        $this->pageHeight = $pageHeight;
        $this->pageTitle = $pageTitle;
        $this->objectId = $objectId;
        $this->getFieldsForEdit();
		$this->getLinks();
    }
    
    public function initalizeFields($return = false) {
		ob_start();
    	if (is_array($this->fields_def)) 
             foreach ($this->fields_def as $name => $def)
			 {
        	     if (!isset($def['editor_str'])) continue;
                 $init = $def['editor_str'].'_init';
        		 if (function_exists($init)) 
        		         $init($def, $this->getObjectValue($name), $this->objectId, $this->catalog, $this->objectDefinition->getTable(), Application::getInstance()->getUser());
        	 }
		$res = $this->initData."\n\n".ob_get_contents();
		ob_end_clean();
		
		if ($return) return $res;
		else echo $res;		
    }
	
	public function saveFields($return = false) {
		ob_start();
    	if (is_array($this->fields_def)) 
             foreach ($this->fields_def as $name => $def)
			 {
        	     if (!isset($def['editor_str'])) continue;
                 $save = $def['editor_str'].'_save';
        		 if (function_exists($save)) 
        		         $save($def);
        	 }
		$res = ob_get_contents();
		ob_end_clean();
		
		if ($return) return $res;
		else echo $res;			
	}
    
    public function setObject($object) {
        $this->object = $object;
        return $this;
    }
    
    private function getObjectValue($name) {
        if ($this->object)
            return $this->object->getDynamicField($name);
            else return null;
                
    }
    
    public function renderFields($page = false, $return = false) {
    
		if (is_array($this->fields_def))
		{ 
			foreach ($this->fields_def as $name => $def)
			{
				$this->addToPage($def);
			}

			if ($page === false)
			{
                 $data = array();
                 foreach ($this->pages as $id => $page) {
    
                      $data[] = '
                      {
                        title:\''.$page['title'].'\',
                        layout:\''.$page['layout'].'\',
                        defaults: {anchor: \'0\'},
                        autoScroll: '.(($this->pageHeight<0 && $page['layout'] != 'fit')?'true':'false').',
                        border    : false,
                        bodyBorder: false,
                        bodyStyle:\'background: none; padding: 5px\',
                        items: [
                            '.implode(',',$page['fields']).'
                        ]
                    }';
                    
                }
                
                $res = implode(',', $data);
                
			} 
			elseif (isset($this->pages[$page]))
			{
            
				$res = implode(',',$this->pages[$page]['fields']);          
            
            }
             
        }  
		if ($return) return $res;
		else echo $res;
    }    
    
    public function addToPage($field) {
    
        if (isset($field['type']) && $field['type'] < 0) return;               
       
        if (!isset($field['editor_str'])) return;
  
        $draw = $field['editor_str'].'_draw';
		
        if (function_exists($draw)) {
            ob_start();
			try {
				$value = $this->getObjectValue($field['name']);
			} catch (\Exception $e) {
				$value = null;				
			}
            $h = $draw($field, $value, $this->objectId, $this->catalog, $this->objectDefinition->getTable(), Application::getInstance()->getUser());
            $data = ob_get_contents();
            ob_end_clean();
            
            if ($h < 0)
			{
                $this->newPage( ($field['page']?$field['page'].' ':'').$field['describ'], 'fit', $data);
                return;
            }            
            
            if ( $field['page'] ) {
            
                $idx = $this->getPageIndexByTitle( $field['page'] );
                if ( $idx < 0 ) $idx = $this->newPage( $field['page'] );
                $this->pages[$idx]['height'] += $h;
                $this->pages[$idx]['fields'][] = $data;            
            
            } else
			{
            
                if ($this->currentPage < 0) {
                    $this->currentPage = $this->newPage($this->pageTitle);
                } 
                                
                if ($this->pageHeight > 0 && $this->pages[$this->currentPage]['height'] + $h > $this->pageHeight) {
                    $this->currentPage = $this->newPage($this->pageTitle.' '.($this->currentPage+2));              
                }
                
                $this->pages[$this->currentPage]['height'] += $h;
                $this->pages[$this->currentPage]['fields'][] = $data;
            
            }
            
        }        
        
    }
    
    public function newPage($title, $layout = 'anchor', $data = false) {
    
        $fields = array();
        if ($data) $fields[] = $data;
    
        $this->pages[] = array(
            'title'  => $title,
            'height' => 0,
            'layout' => $layout,
            'fields' => $fields
        ); 
        
        return count($this->pages) - 1;   
    }   
    
    public function getPageIndexByTitle($title) {
        foreach ( $this->pages as $id => $p ) {
            if ( $p['title'] == $title ) return $id;
        }                    
        return -1;
    } 
    
    private function setObjectDefinition($objectDefinition) 
    {
        $this->objectDefinition = $objectDefinition;
        return $this;
    }    

    private function setCatalog($catalog) 
    {
        $this->catalog = $catalog;
        return $this;
    } 
    
    /**
     * getFieldsForEdit
     * 
     * возвращает список полей в материалах
     * подгружает необходимые редакторы полей 
     * 
     * @return array
     **/
    private function getFieldsForEdit() {
        global $editors, $field_editors, $translator;
        
        if ($this->fields_def) return;
		
		ob_start();
        
        $fields = $this->objectDefinition->getFields($this->catalog);
        
        $this->fields_def = array();   
        
        foreach ($fields as $id => $f) {         

            if (!$f['shw'] &&  $f['name'] != 'alias') continue;
    
            $this->fields_def[$id] = $f;
          
        	$_editor = (int)$f['editor'];
          
        	if ($f['pseudo_type']) 
                $f['type'] = $f['pseudo_type'];
              
        	if (isset($editors[EDITOR_HIDDEN])) 
                $this->fields_def[$id]['editor_str'] = $editors[EDITOR_HIDDEN];
              
        	if (isset($field_editors[$f['type']]) && is_array($field_editors[$f['type']])) {
          
        	    if (!in_array($_editor, $field_editors[$f['type']])) 
        			   $_editor = $field_editors[$f['type']][0];
                 
        		  if ($_editor == EDITOR_USER) {
        		      if (file_exists(PLUGIN_MATH_DIR.'/'.$f['editor_user'].'.php')) {
        				      $this->fields_def[$id]['editor_str'] = $f['editor_user'];
        				      include_once(PLUGIN_MATH_DIR.'/'.$f['editor_user'].'.php');	        
        		      } 
					  else {
						  $_editor = $field_editors[$f['type']][0];
					  }
        	    }
        
            	if ( isset($editors[$_editor]) ) {
            	    $this->fields_def[$id]['editor_str'] = $editors[$_editor];
            	    if ( file_exists('editors/'.$editors[$_editor].'.php') ) 
                        include_once('editors/'.$this->fields_def[$id]['editor_str'].'.php');
            	}
        	}
        }
		
		$this->initData = ob_get_contents();
		ob_end_clean();		
    }

    /**
     * getLinkTypes
     * 
     * возвращает список типов материалов в которых есть поля типа FIELD_LINK или FIELD_MATERIAL - ссылки на редактируемый материал
     * 
     * @return array
     **/	
	private function getLinks()
	{
		if (!$this->objectId) return;

		// поля типа FIELD_MATERIAL
		$fields = self::getDbConnection()->fetchAll('SELECT * FROM types_fields WHERE type='.FIELD_MATERIAL.' and pseudo_type=0 and len='.$this->objectDefinition->id);		
		
		// поля типа PSEUDO_FIELD_TAGS
		$fields2 = self::getDbConnection()->fetchAll('SELECT * FROM types_fields WHERE pseudo_type='.PSEUDO_FIELD_TAGS.' and len='.$this->objectDefinition->id);		
		$fields = array_merge($fields, $fields2);		
		
		// поля типа FIELD_LINK или FIELD_LINKSET
		if ($this->catalog > 0) {		
			$catalog = Catalog::getById($this->catalog);			
			// итератор всех разделов имеющих тип материалов как у редактируемого материала
			$sections = new Iterator\Catalog\Catalog();
			$sections->where('typ='.$this->objectDefinition->id);
			if (!count($sections)) return;
			
			// получаем все поля, ссылающиеся на эти разделы
			$data = self::getDbConnection()->fetchAll('SELECT * FROM types_fields WHERE type IN ('.FIELD_LINK.','.FIELD_LINKSET.') and pseudo_type=0 and len IN ('.implode(',',$sections->idArray()).')');

			foreach ($data as $f) {				
				$c = Catalog::getById($f['len']);
				// оставляем только поля, ссылающиеся на раздел текущего материала или его родителей
				if ($catalog->path->has( $c )) {
					$fields[] = $f;
				}				
			}			
		}	
		
		ob_start();
		foreach ($fields as $f) try {			
			$od = ObjectDefinition::findById($f['id']);		
			$f['describ'] = $od->getDescriptionDisplay();
			$f['editor_str'] = 'editor_linkset_link';
			if ( file_exists('editors/'.$f['editor_str'].'.php') ) 
				include_once('editors/'.$f['editor_str'].'.php');
			$this->fields_def[] = $f;
		} catch (\Exception $e) {}
		$this->initData .= ob_get_contents();
		ob_end_clean();					
	}

}
