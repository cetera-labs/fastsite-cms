<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
namespace Cetera; 

/**
 * Класс реализующий управление типами материалов системы 
 *
 * @property-read int $id идентификатор типа материалов
 * @property-read string $table таблица, в которой хранятся объекты
 * @property-read string $alias алиас типа материалов === таблице
 * @property-read string $description описание типа материалов
 * @property-read boolean $fixed cистемный тип материалов (нельзя удалить из админки)
 **/
class ObjectDefinition extends Base {
	
	use DbConnection;

	/**
	 * Пользовательские классы для определенных типов материалов
	 * @internal
	 */  
    public static $userClasses = array();
	
	public static $plugins = array();

	/**
	 * @internal
	 */    
    protected $_table = null;
	
	/**
	 * @internal
	 */	
    protected $_description = null; 
	
	/**
	 * @internal
	 */	
    protected $_fixed = null;
	
	/**
	 * @internal
	 */	
    protected $_handler = null; 
    
	/**
	 * @internal
	 */
    public static $reserved_aliases = array(
      	'materials',
      	'material_files',
      	'material_links',
      	'material_tags'
    );  
    
    /**
     * Описание полей типа материалов 
     *         
     * @var array    
     */  
    private $fields_def = null; 
    
	/**
	 * @internal
	 */	
    private $field_def = array(
        FIELD_TEXT     => 'varchar(%)',
        FIELD_LONGTEXT => 'MEDIUMTEXT',
        FIELD_HUGETEXT => 'LONGTEXT',
        FIELD_INTEGER  => 'int(11)',
        FIELD_DOUBLE   => 'double',
        FIELD_FILE     => 'varchar(1024)',
        FIELD_DATETIME => 'datetime',
        FIELD_LINK     => 'int(11)',
        FIELD_BOOLEAN  => "tinyint DEFAULT '0'",
        FIELD_ENUM     => "ENUM(%)",
        FIELD_MATERIAL => 'int(11)',
    );
	
  	public static function enum()
    {   
		$res = [];
		$r = DbConnection::getDbConnection()->query('SELECT * FROM types');
		while ($f = $r->fetch()) {
			$t = self::findById($f['id']);
			$t->setData($f);
			$res[] = $t;
		}
		return $res;
  	}	
    
	/**
	 * Найти тип материалов по имени таблицы в БД
	 *
	 * @param string $table Таблица БД типа материалов
	 * @return \Cetera\ObjectDefinition
	 */		
    public static function findByTable($table)
    {
        $od = new self(null, $table);
        $od->getId();
        return $od;
    }    
    
	/**
	 * Найти тип материалов по alias
	 *
	 * @param string $alias	Alias типа материалов 
	 * @return \Cetera\ObjectDefinition
	 */		
    public static function findByAlias($alias)
    {
        return self::findByTable($alias);
    }  
    
	/**
	 * Найти тип материалов по ID
	 *
	 * @param int $id ID типа материалов	 
	 * @return \Cetera\ObjectDefinition
	 */		
    public static function findById($id)
    {
        return new self($id);
    }   
	
	/**
	 * Найти тип материалов по ID
	 *
	 * @param int $id ID типа материалов	 
	 * @return \Cetera\ObjectDefinition
	 */			
    public static function getById($id)
    {
        return new self($id);
    } 	

	/**
	 * Зарегистрировать пользовательский класс для определенного типа материалов
	 *
	 * @param int $id ID типа материалов или имя класса. Класс должен быть наследником \Cetera\MaterialUser
     * @param string $className Имя класса. Класс должен быть наследником \Cetera\Material
	 * @return void
	 */		
    public static function registerClass($id, $className = null)
    {
        if (is_subclass_of($id, '\Cetera\MaterialUser') ) {
            $tid = call_user_func([$id, 'getTypeId']);
            self::$userClasses[$tid] = $id;
        }
        else {
		    if (! is_subclass_of($className, '\Cetera\Material') ) throw new \Exception('Класс '.$className.' должен быть наследником \Cetera\Material');
		    self::$userClasses[$id] = $className;
        }
    }	
    
	/**
	 * Создает новый тип материалов
	 *
	 * @param array $params параметры типа материалов:<br>
	 * alias - alias типа, он же название создаваемой таблицы БД под этот тип материалов<br>
	 * fixed - системный тип (невозможно удалить из админки)<br>
	 * @return ObjectDefinition
	 * @throws Exception\CMS если тип с таким alias уже существует
	 * @throws Exception\CMS если alias зарезервирован
	 */		
    public static function create()
    {
		$params = func_get_arg(0);
		
        $params = self::fix_params($params);
        
      	if (!$params['fixed'] && in_array($params['alias'], self::$reserved_aliases)) {
            throw new Exception\CMS(Exception\CMS::TYPE_RESERVED);
        }
      	
		$conn = DbConnection::getDbConnection();
		
        $r  = $conn->fetchAll("select id from types where alias='".$params['alias']."'");
        if (count($r)) throw new Exception\CMS(Exception\CMS::TYPE_EXISTS);
        
        $conn->executeQuery('DROP TABLE IF EXISTS '.$params['alias']);
        
      	$conn->executeQuery("create table ".$params['alias']." (
            id int(11) NOT NULL auto_increment, idcat int(11), dat datetime, dat_update datetime, name varchar(2048),
         		type int(11), autor int(11) DEFAULT '0' NOT NULL, tag int(11) DEFAULT '1' NOT NULL, alias varchar(255) NOT NULL, 
            PRIMARY KEY (id), KEY idcat (idcat), KEY dat (dat), KEY alias (alias)
        )"); 

        $conn->executeQuery(
			"INSERT INTO types (alias,describ, fixed) values (?,?,?)",
			array($params['alias'], $params['describ'], (int)$params['fixed'])
		);
        
		$id = $conn->lastInsertId();
            
        $translator = Application::getInstance()->getTranslator();              
            
    	$conn->executeQuery("insert into types_fields (id,name,type,describ,len,fixed,required,shw,tag) values ($id,'tag',        3,'Sort[ru=Сортировка]',  1, 1, 0, 1, 1)");
    	$conn->executeQuery("insert into types_fields (id,name,type,describ,len,fixed,required,shw,tag) values ($id,'name',       1,'Title[ru=Заголовок]',       99, 1, 0, 1, 2)");
    	$conn->executeQuery("insert into types_fields (id,name,type,describ,len,fixed,required,shw,tag) values ($id,'alias',      1,'Alias',     255, 1, 1, 1, 3)");
    	$conn->executeQuery("insert into types_fields (id,name,type,describ,len,fixed,required,shw,tag) values ($id,'dat',        5,'Date create[ru=Дата создания]', 1, 1, 1, 0, 5)");
    	$conn->executeQuery("insert into types_fields (id,name,type,describ,len,fixed,required,shw,tag) values ($id,'dat_update', 5,'Edit date[ru=Дата изменения]', 1, 1, 1, 0, 6)");
    	$conn->executeQuery("insert into types_fields (id,name,type,describ,len,fixed,required,shw,tag,pseudo_type) values ($id,'autor', 6,'Author[ru=Автор]', -2, 1, 1, 0, 4,1003)");
    	$conn->executeQuery("insert into types_fields (id,name,type,describ,len,fixed,required,shw,tag) values ($id,'type',       3,'Properties[ru=Свойства]', 1, 1, 1, 0, 7)");
        $conn->executeQuery("insert into types_fields (id,name,type,describ,len,fixed,required,shw,tag,pseudo_type) values ($id,'idcat',      6,'Section[ru=Раздел]', 0, 1, 1, 0, 8, 1008)");

        self::regenerateClasses();

        return new self($id, $params['alias']);
      
    }          

	/**
	 * @internal
	 */		
    public function __construct($id = null, $table = null) 
    {
    
        if (!$id && !$table) throw new Exception\CMS('One of $id or $table must be specified.');
        
        if (!$table && $id && !(int)$id) {
            $table = $id;
            $id = null;
        }
        
        if ($id && is_array($id)) {
            throw new \Exception('ID must be integer');
        }
    
        $this->_id = $id;
        $this->_table = $table;
    }
    
	/**
	 * @internal
	 */	
    public function getId()
    {
        if (!$this->_id) {
            $r = DbConnection::getDbConnection()->fetchAssoc('select * from types where alias=?',[$this->table]);
    		if ($r) 
          		$this->setData($r);   	
                else throw new Exception\CMS('Type "'.$this->table.'" is not found.');        
        }
        return $this->_id;
    }
    
	/**
	 * @internal
	 */	
    private function fetchData()
    {
    	$r = DbConnection::getDbConnection()->fetchAssoc("select * from types where id=?",[$this->id]);
        if ($r) {
    	    $this->setData($r);                
    	} 
		else {
			throw new Exception\CMS('Materials table for type '.$this->id.' is not found.');     
		}
    }
    
	/**
	 * @internal
	 */	
    private function setData($f) 
    {
        $this->_id          = $f['id']; 
        $this->_table       = $f['alias'];  
        $this->_description = $f['describ'];  
        $this->_fixed       = $f['fixed'];   
    }
    
	/**
	 * @internal
	 */	
    public function getTable()
    {
        if (null === $this->_table) $this->fetchData();
        return $this->_table;    
    } 
    
	/**
	 * @internal
	 */	
    public function getAlias()
    {
        return $this->getTable();    
    }     
    
	/**
	 * @internal
	 */	
    public function getDescription()
    {
        if (null === $this->_description) $this->fetchData();
        return $this->_description;    
    } 
	
   
	/**
	 * @internal
	 */	
    public function getFixed()
    {
        if (null === $this->_fixed) $this->fetchData();
        return $this->_fixed;    
    } 
    

	
    public function setPlugin($file)
    {
        if (!isset(self::$plugins[$this->getId()])) {
			self::$plugins[$this->getId()] = [];
		}
		self::$plugins[$this->getId()][] = $file;
    } 	
	
    public function getPlugins()
    {
        if (!isset(self::$plugins[$this->getId()])) {
			return [];
		}
		return self::$plugins[$this->getId()];
    } 	              
    
	/**
	 * Возвращает все поля данного типа материалов
	 *
	 * @param int|Catalog $dir если указан раздел, то учитывается видимость полей, заданная для этого раздела
	 * @return array
	 */		
    public function getFields( $dir = null ) {
        $inherit = false;
        if ($dir !== false && !is_a($dir, 'Cetera\\Catalog') && (int)$dir && $dir > 0) $dir = Catalog::getById($dir); 
        if (is_a($dir, 'Cetera\\Catalog'))
		{
            while ($dir->inheritFields && !$dir->isRoot()) $dir = $dir->getParent();
            if (!$dir->inheritFields) {
                $r = DbConnection::getDbConnection()->fetchAll('SELECT * FROM types_fields_catalogs WHERE type_id=? and catalog_id=?',[$this->id,$dir->id]);
                $inherit = array();
                foreach ($r as $f) $inherit[$f['field_id']] = $f;
            }
        }   
        
        $res = $this->_get_fields();
        
        if ($inherit && count($inherit)) {
            foreach ($res as $key => $val) {
            
                if (isset($inherit[$val['field_id']])) {
                     if ($inherit[$val['field_id']]['force_hide'])
                         $res[$key]['shw'] = 0; 
                     if ($inherit[$val['field_id']]['force_show'])
                         $res[$key]['shw'] = 1; 
                }            
            
            }
        }
        
        return $res;
         
    } 
    
	/**
	 * Возвращает поле данного типа материалов
	 *
	 * @param string $fieldName имя поля
	 * @return ObjectField
	 */		
    public function getField($fieldName) {
        $fields = $this->_get_fields();
        if (isset($fields[$fieldName])) {
            return  $fields[$fieldName];
        } else {
            throw new \Exception('Поле "'.$fieldName.'" не найдено');
        }
    } 
	
	/**
	 * Возвращает true если существует поле у данного типа материалов
	 *
	 * @param string $fieldName имя поля
	 * @return boolean
	 */		
    public function hasField($fieldName) {
        $fields = $this->_get_fields();
        return isset($fields[$fieldName]);
    } 	
    
	/**
	 * Удаляет тип материалов
	 */		
    public function delete() {
        $r = DbConnection::getDbConnection()->fetchAll( "select id from dir_data where typ=?", [$this->id] );
        foreach ($r as $f) {
            $c = Catalog::getById($f[0]);
            $c->delete();
        }
        DbConnection::getDbConnection()->executeQuery("drop table ".$this->table);
        DbConnection::getDbConnection()->executeQuery("delete from types where id=".$this->id);
        DbConnection::getDbConnection()->executeQuery("delete from types_fields where id=".$this->id);
        
        // удалить все поля - ссылки на материалы этого типа
        $r = DbConnection::getDbConnection()->fetchAll('SELECT A.field_id, A.name, B.alias FROM types_fields A, types B WHERE A.type=? and A.len=? and A.id=B.id', [FIELD_MATSET, $this->id]);
        foreach ($r as $f) {
            DbConnection::getDbConnection()->executeQuery('DROP TABLE '.$f['alias'].'_'.$alias.'_'.$f['name']);
      	    DbConnection::getDbConnection()->executeQuery('DELETE FROM types_fields WHERE field_id='.$f['field_id']);
        } 
        self::regenerateClasses();        
    }
    
	/**
	 * Изменяет тип материалов
	 *
	 * @param array $params параметры типа материалов:<br>
	 * alias - alias типа, он же название создаваемой таблицы БД под этот тип материалов<br>
	 * fixed - системный тип (невозможно удалить из админки)<br>
	 * @return ObjectDefinition	 
	 */		
    public function update($params) {
    
        $params = self::fix_params($params);
      
    	$oldalias = $this->getAlias();
        $alias = $params['alias'];
         
        if ($alias != $oldalias) { // Переименуем тип
        
          	if (!$params['fixed'] && in_array($params['alias'], self::$reserved_aliases)) {
                throw new Exception\CMS(Exception\CMS::TYPE_RESERVED);
            }
              
        	$r  = DbConnection::getDbConnection()->fetchAll("select id from types where alias=?",[$alias]);
        	if (count($r)) throw new Exception\CMS(Exception\CMS::TYPE_EXISTS);
    
        	$r  = DbConnection::getDbConnection()->fetchAll("select A.alias, B.name from types A, types_fields B, dir_data C where C.typ=".$this->id." and C.id=B.len and B.type=7 and A.id=B.id");
        	foreach ($r as $f) {
        	    DbConnection::getDbConnection()->executeQuery('ALTER TABLE '.$f['alias'].'_'.$oldalias.'_'.$f['name'].' RENAME '.$f['alias'].'_'.$alias.'_'.$f['name']);
        	}
    
        	$r  = DbConnection::getDbConnection()->fetchAll("select A.alias, B.name from types A, types_fields B, dir_data C where C.typ=B.len and C.id=A.id and B.type=7 and B.id=".$this->id);
        	foreach ($r as $f) {
        	    DbConnection::getDbConnection()->executeQuery('ALTER TABLE '.$oldalias.'_'.$f['alias'].'_'.$f['name'].' RENAME '.$alias.'_'.$f['alias'].'_'.$f['name']);
        	}
    
        	$r  = DbConnection::getDbConnection()->fetchAll("select A.alias, B.name from types A, types_fields B where B.type=8 and A.id=B.id and B.len=".$this->id);
        	foreach ($r as $f) {
        	    DbConnection::getDbConnection()->executeQuery('ALTER TABLE '.$f['alias'].'_'.$oldalias.'_'.$f['name'].' RENAME '.$f['alias'].'_'.$alias.'_'.$f['name']);
        	}
    
        	$r  = DbConnection::getDbConnection()->fetchAll("select A.alias, B.name from types A, types_fields B where B.type=8 and B.id=".$this->id." and B.len=A.id");
        	foreach ($r as $f) {
        	    DbConnection::getDbConnection()->executeQuery('ALTER TABLE '.$oldalias.'_'.$f['alias'].'_'.$f['name'].' RENAME '.$alias.'_'.$f['alias'].'_'.$f['name']);
        	}
    
        	DbConnection::getDbConnection()->executeQuery("ALTER TABLE $oldalias RENAME $alias");
			DbConnection::getDbConnection()->update('types', array('alias'=>$alias), array('id'=>$this->id));
			  
			$this->_table = $alias;
    	  } // if
    
        $sql = array();
        if (isset($params['fixed'])) {
			$sql[] = 'fixed='.(int)$params['fixed'];
			$this->_fixed = (int)$params['fixed'];
		}
        if (isset($params['describ'])) {
			$sql[] = 'describ='.DbConnection::getDbConnection()->quote($params['describ']);
			$this->_description = $params['describ'];
		}
        
        if (count($sql)) {
            DbConnection::getDbConnection()->executeQuery('update types set '.implode(',',$sql).' where id='.$this->id);
            self::regenerateClasses();
        }
        
        return $this;
    }  
     
	/**
	 * Добавляет новое поле в тип материалов
	 *
	 * @internal
	 * @param array $params параметры поля
	 */	 
    public function addField($params)
    {
          
        $params = self::fix_field_params($params);
        
        if ($params['type'] > 0) {
        
            $r = DbConnection::getDbConnection()->fetchColumn('SELECT COUNT(*) FROM types_fields WHERE id=? and name=?',[$this->id,$params['name']],0);
            if ( $r>0 ) throw new Exception\CMS(Exception\CMS::FIELD_EXISTS);
        
            $alias = $this->table;
            if ( $params['type'] != FIELD_LINKSET && $params['type'] != FIELD_LINKSET2 && $params['type'] != FIELD_MATSET )
			{					
                $params['len'] = stripslashes($params['len']);
                $def = str_replace('%',$params['len'],$this->field_def[$params['type']]);
                $sql = "ALTER TABLE $alias ADD `".$params['name']."` $def";
                $params['len'] = (integer) $params['len'];
                DbConnection::getDbConnection()->executeQuery($sql);
            } 
			else 
			{    			
                self::create_link_table($alias, $params['name'], $params['type'], $params['len'], $this->id, $params['pseudo_type']);
            }
            
        }
        
		$tag = DbConnection::getDbConnection()->fetchColumn("select max(tag) from types_fields where id=?",[$this->id],0) + 10;
        DbConnection::getDbConnection()->executeQuery(
			"INSERT INTO types_fields (tag,name,type,pseudo_type,len,describ,shw,required,fixed,id,editor,editor_user, default_value, page) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
			[$tag,$params['name'],$params['type'],$params['pseudo_type'],$params['len'],$params['describ'],$params['shw'],$params['required'],$params['fixed'],$this->id,(int)$params['editor'],$params['editor_user'],$params['default_value'],$params['page']]
		);
        self::regenerateClasses();
		return DbConnection::getDbConnection()->lastInsertId();
    } 
       
	/**
	 * Изменяет поле в типе материалов
	 *
	 * @internal
	 * @param array $params параметры поля
	 */	 	   
    public function updateField($params)
    {
        
        $params = self::fix_field_params($params);

        $alias = $this->table;
            
		$f = DbConnection::getDbConnection()->fetchArray('SELECT type,len,name FROM types_fields WHERE field_id='.$params['field_id']);
        if (!$f) throw new Exception\CMS(Exception\CMS::EDIT_FIELD);
                
        $type_old = $f[0];
        $len_old  = $f[1];
        $name_old = $f[2];
          
		if (isset($params['type']) && $params['type']) {
			
			if ($type_old != $params['type']) {
					
						  // изменился тип поля
						
						  if ($params['type'] != FIELD_LINKSET && $params['type'] != FIELD_LINKSET2 && $params['type'] != FIELD_MATSET) {
						
							  $params['len'] = stripslashes($params['len']);
							  $def = str_replace('%',$params['len'],$this->field_def[$params['type']]);
							  $params['len'] = (integer) $params['len'];
							
								if ($type_old == FIELD_LINKSET || $type_old == FIELD_LINKSET2 || $type_old == FIELD_MATSET) {
								  
								if ($params['type'] >= 0) 
									$action = 'ADD';
									else $action = false;
								  
									self::drop_link_table($alias, $name_old, $type_old, $len_old, $this->id, $params['pseudo_type']);
								
							} 
							else {
							
								if ($type_old >= 0) { 
							
									if ($params['type'] >= 0) 
									{
										$action = "CHANGE `".$name_old."`";
									}
									else
									{
										DbConnection::getDbConnection()->executeQuery("alter table `$alias` drop `".$name_old."`");
										$action = false;
									}
									
								} else {
								
									$action = 'ADD';
								
								}
									   
							}                     
							
							if ($action)
									DbConnection::getDbConnection()->executeQuery("alter table `$alias` $action `".$params['name']."` $def");
								
						  } else {
						
							if ($type_old >= 0) {
								if ($type_old != FIELD_LINKSET && $type_old != FIELD_LINKSET2 && $type_old != FIELD_MATSET ) {
									  DbConnection::getDbConnection()->executeQuery("alter table `$alias` drop `".$name_old."`");
								} 
								else {
									  self::drop_link_table($alias, $name_old, $type_old, $len_old, $this->id, $params['pseudo_type']);
								}
							}
							self::create_link_table($alias, $params['name'], $params['type'], $params['len'], $this->id, $params['pseudo_type']);
							
						  }
					
			} 
			elseif ($type_old >= 0 && ($params['name'] != $name_old || $params['len'] != $len_old)) {
					
						  if ($params['type']!=FIELD_LINKSET && $params['type']!=FIELD_LINKSET2 && $params['type']!=FIELD_MATSET) {
							  $params['len'] = stripslashes($params['len']);
							  $def = str_replace('%',$params['len'],$this->field_def[$params['type']]);
							  $sql = "alter table `$alias` change `".trim($f[2])."` `".$params['name']."` $def";
							  $params['len'] = (integer) $params['len'];
						  } 
						  else {
							  $tbl = self::get_table($params['type'], $params['len'], $this->id,$params['pseudo_type']);
							  $tbl1 = self::get_table($f[0],$f[1], $this->id,$params['pseudo_type']);
							  $sql = "alter table ".$alias."_".$tbl1."_".$f[2]." rename ".$alias."_".$tbl."_".$params['name'];
						  }
						  DbConnection::getDbConnection()->executeQuery($sql);
					  
			}
			
			$sql = "UPDATE types_fields 
					SET name='".$params['name']."',
						type=".$params['type'].",
						pseudo_type=".$params['pseudo_type'].",
						len=".$params['len'].",
						describ='".$params['describ']."',
						shw=".$params['shw'].",
						required=".$params['required'].",
						default_value='".$params['default_value']."',
						editor=".$params['editor'].",
						editor_user='".$params['editor_user']."',
						page='".$params['page']."',
						tag='".(int)$params['tag']."'
					WHERE field_id=".$params['field_id'];			
		}
		else 
		{
			$sql = "UPDATE types_fields 
					SET 
						describ='".$params['describ']."',
						default_value='".$params['default_value']."',
						page='".$params['page']."'
					WHERE field_id=".$params['field_id'];			
		}
		
		DbConnection::getDbConnection()->executeQuery($sql);
        self::regenerateClasses();
    }  

    /**
     * Возвращает материалы данного типа     
     *        
     * @return Iterator\Material    
     */       
    public function getMaterials()
    {    
		if ($this->table == 'users') return new Iterator\User();
		if ($this->table == 'dir_data') return new Iterator\Catalog\Catalog();
        return new Iterator\Material($this);
    }
	
    /**
     * Возвращает разделы с материалами данного типа   
     *        
     * @return Iterator\Catalog
     */       
    public function getCatalogs()
    {    
		$list = new Iterator\Catalog\Catalog();
        return $list->where('typ='.$this->id);
    }		
    
    /**
     * @internal
     **/
    private function _get_fields() {
    
        if (!$this->fields_def) {	
			$this->fields_def = [];
			$r = DbConnection::getDbConnection()->fetchAll('SELECT * FROM types_fields WHERE id=? ORDER BY tag', [$this->id]);
			foreach($r as $f) {
				$key = $f['name'];
				while (isset($this->fields_def[$key])) $key .= '_';
				$this->fields_def[$key] = ObjectField::factory($f, $this);
			} 
        }
        return $this->fields_def;
    }
   
    /**
     * @internal
     **/   
    public static function fix_params($params)
    {
        if (!isset($params['describ']) && isset($params['description'])) $params['describ'] = $params['description']; 
        if (!isset($params['alias']) && isset($params['name'])) $params['alias'] = $params['name']; 
        if (!isset($params['alias']) && isset($params['table'])) $params['alias'] = $params['table']; 
		$params['alias'] = str_replace(' ','_',$params['alias']);
        return $params;    
    }   
    
    /**
     * @internal
     **/	
    public static function fix_field_params($params)
    {
        foreach ($params as $pname => $pvalue) {
            $params[ObjectField::fixOffset($pname)] = $pvalue;
        }
        
        if (!(int)$params['len'] && $params['len'] && $params['type'] != FIELD_ENUM) {
            $od = self::findByAlias($params['len']);
            $params['len'] = $od->id;
        } 
       
        
        return $params;    
    } 
    
    /**
     * @internal
     **/	
    public static function create_link_table($fieldtable, $fieldname, $type, $len, $id, $pseudo_type = 0) {
		if ($type == FIELD_LINKSET2) {
			DbConnection::getDbConnection()->executeQuery("CREATE TABLE IF NOT EXISTS ".$fieldtable."_".$fieldname." (id int(11) not null, dest_type int(11) not null, dest_id int(11) not null, tag int(11) DEFAULT '0' NOT NULL, PRIMARY KEY (id, dest_type, dest_id), key dest (dest_type, dest_id))");
		}
		else {
			$tbl = self::get_table($type,$len, $id,$pseudo_type);
			DbConnection::getDbConnection()->executeQuery("CREATE TABLE IF NOT EXISTS ".$fieldtable."_".$tbl."_".$fieldname." (id int(11) not null, dest int(11) not null, tag int(11) DEFAULT '0' NOT NULL, PRIMARY KEY (id, dest), key dest (dest))");
		}
    }
    
    /**
     * @internal
     **/	
    public static function drop_link_table($fieldtable, $fieldname, $type, $len, $id, $pseudo_type = 0) {
		if ($type == FIELD_LINKSET2) {
			DbConnection::getDbConnection()->executeQuery("DROP TABLE IF EXISTS ".$fieldtable."_".$fieldname);
		}
		else {
			$tbl = self::get_table($type,$len, $id, $pseudo_type);
			DbConnection::getDbConnection()->executeQuery("DROP TABLE IF EXISTS ".$fieldtable."_".$tbl."_".$fieldname);
		}
    }
    
    /**
     * @internal
     **/	
    public static function get_table($field_type, $len, $type_id, $pseudo_type = 0) { 
      if ($pseudo_type == PSEUDO_FIELD_CATOLOGS) return Catalog::TABLE;
       
    	if ($field_type == FIELD_LINKSET && $len) {
    	    if ($len == CATALOG_VIRTUAL_USERS) return User::TABLE;
    	  	$r = DbConnection::getDbConnection()->fetchColumn("select A.alias from types A, dir_data B where A.id = B.typ and B.id=?",[$len],0);
    	} else {
    	  if ($field_type == FIELD_LINKSET) $len = $type_id;
    	  $r = DbConnection::getDbConnection()->fetchColumn("select alias from types where id=?",[$len],0);
    	}
    	return $r;
    }

	/**
	 * @internal
	 */	
    public function getDescriptionDisplay()
    {
		return Application::getInstance()->decodeLocaleString( $this->getDescription() ); 
    } 		

	public function toArray()
	{
        return array(
            'id'      => (int)$this->id,
            'alias'   => $this->alias,
            'describ' => $this->description,
			'describDisplay' => $this->descriptionDisplay,
            'fixed'   => (int)$this->fixed
        );		
	}
    
    public static function regenerateClasses() {
        $entityManager = Application::getInstance()->getEntityManager();
        $d = $entityManager->getConfiguration()->getMetadataDriverImpl();
        $d->generateClasses();        
    }

}
