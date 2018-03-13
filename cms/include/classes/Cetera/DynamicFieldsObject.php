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
 * Абстрактный класс для объектов системы с настраиваемыми полями (Материалы, Пользователи).
 *  
 * @package CeteraCMS
 * @abstract
 * @internal
 */ 
abstract class DynamicFieldsObject extends Base implements \ArrayAccess {

    use DbConnection;
    
    /**
     * Значения полей объекта 
     *         
     * @var array    
     */  
    public $fields = array();
   
    /**
     * Значения полей объекта? считанные из БД 
     *         
     * @var array    
     */      
    public $raw_fields = array();

    /**
     * Описание объекта 
     *         
     * @var array    
     */  
    public $objectDefinition = FALSE;
    
    /**
     * Созданные экземпляры объектов 
     * 
     * @internal	 
     * @var array    
     */  	
    protected static $instances = array();
        
    /**
     * Создает экземпляр нужного класса в зависимости от "Типа материалов".
     *       
     * @internal	 
     * @param int|ObjectDefinition $type "Тип материалов" объекта
     * @param string $table Таблица БД, в которой хранятся объекты         
     * @return DynamicFieldsObject       
     */ 
    public static function factory($type=0, $table = null, $fields = null)
    {       
        if ($type instanceof ObjectDefinition) {
            $type_id = $type->id;
            $od = $type;
        } elseif ((int)$type) {
            $type_id = (int)$type;
            $od = null;
        }
            
        switch ($type_id) {
            case User::TYPE:
                $o = User::create();
                break;
            case Catalog::TYPE:
				if ($fields && $fields['is_server'])
				{
					$o  = Server::create();
					break;
				}
                $o = Catalog::create();
                break;
            default:
                if (!$od) $od = new ObjectDefinition($type_id, $table);
				if ( isset(ObjectDefinition::$userClasses[$type_id]) ) {
					$uc = ObjectDefinition::$userClasses[$type_id];
					$o = $uc::create($od);
				} else {
					$o = Material::create($od);
				}
                break;
        }

        return $o;
    }
        
    /**
     * Создает экземпляр нужного класса в зависимости от "Типа материалов",
     * поля загружаются из БД, резульат кэшируется     
     *    
     * @internal	 
     * @param array $data поля объекта
     * @param int|ObjectDefinition $type "Тип материалов" объекта
     * @param string $table Таблица БД, в которой хранятся объекты         
     * @return DynamicFieldsObject       
     */     
    public static function fetch($data, $type = 0, $table = null)
    {
        if (is_array($data)) {
        
            $id = (int)$data['id'];
            $fields = $data;
        
        } else {
        
            $id = (int)$data;
            $fields = null;
            
        }
    
        if ($type instanceof ObjectDefinition) {
            $type_id = $type->id;
        } else {
            $type_id = $type;
        } 
        
        if (!$id || $fields || !isset(self::$instances[$type_id][$id]))
		{
            $o = static::factory($type, $table, $fields);
            if ($id) {
                if (!$fields) $o->load($id);            
                self::$instances[$type_id][$id] = $o;
            }
                    
        } else {
        
            $o = self::$instances[$type_id][$id];
        
        }
        
        if ($fields) {
            $o->setFields($fields);
        }
        
        return $o; 
    }
    
    /**
     * Устанавливает поля объекта  
     *       
     * @internal	 
     * @param array $fields поля объекта     
     */	
    public function setFields($fields)
    {
        $this->fields = $fields; 
        $this->raw_fields = array_merge($this->raw_fields, $fields);   
        parent::setFields($fields);    
    }   
    
    /**
     * Загружает из БД объект  
     *       
     * @internal 	 
     * @param int $id ID объекта     
     */		
    private function load($id)
    {
        $fields = $this->getDbConnection()->fetchAssoc('SELECT * FROM `'.$this->objectDefinition->table.'` WHERE id = ?', array($id));
        if (!$fields) throw new \Exception('Object '.$this->objectDefinition->table.':'.$id.' is not found'); 
        $this->setFields($fields);
        $this->_id = $id;
        return $this;
    }
    
    /**
     * Возвращает объект по ID и "Типу материалов".
     *                 
     * @param int $id ID объекта
     * @param int $type "Тип материалов" объекта
     * @param string $table Таблица БД, в которой хранятся поля объекта         
     * @return DynamicFieldsObject       
     */ 
	public static function getByIdType($id, $type)
    {
        $o = self::factory($type);
		$o->load($id);
        return $o;
    }
        
    /**
     * Конструктор  
     * 
	 * @internal
     * @param ObjectDefinition $od "Тип материалов" объекта      
     * @return void        
     */          
    protected static function create() 
    {
		$od = func_get_arg(0);
		
        $o = parent::create();
		$o->objectDefinition = $od;
		return $o;
    }
    
    /**
     * @internal      
     */ 	
    public function __toString()
    {
        return json_encode(array(
            'id'   => $this->id,
            'name' => $this->name
        ));
    }      
    
    /**
     * Перегрузка чтения свойств класса. 
     * 
     * Организуется доступ к полям объекта, созданным в конструкторе "Типы материалов" CeteraCMS, 
     * как к свойствам объекта                     
     * 
  	 * @internal  
     * @param string $name свойство класса          
     * @return mixed
     * @throws LogicException          
     */ 
    public function __get($name)
    {
        try {
        
            return parent::__get($name);
            
        } catch (\LogicException $e) {
        
            return $this->getDynamicField($name);
        }
    }
    
    /**
  	 * @internal          
     */ 	
    public function __isset ( $name )
    { 
    
        return array_key_exists ( $name , $this->getFieldsDef() ) || property_exists($this, '_'.$name);
    
    }    
    
    /**
     * Перегрузка записи свойств класса.  
     *        
     * Организуется доступ к полям объекта, созданным в конструкторе "Типы материалов" CeteraCMS, 
     * как к свойствам объекта  
     *          
     * @internal  	 
     * @param string $name свойство класса   
     * @param mixed $value значение свойства           
     * @return void
     * @throws LogicException          
     */ 
    public function __set($name, $value)
    {
        try {
        
            parent::__set($name, $value);
            
        } catch (\LogicException $e) {
    
            if (isset($this->fields[$name]))
                $this->fields[$name] = $value;
                else {
                    if (!in_array($name,array_keys($this->fields)))
                        throw new \LogicException("Property {$name} is not found");
                        else $this->fields[$name] = $value;
                }
        }
    }
    
    /**    
     * @internal  	     
     */ 	
    public static function clearLocks()
    {
        self::getDbConnection()->query('DELETE FROM `lock` WHERE dat < NOW() - INTERVAL 11 SECOND');
    }    
    
    /**    
     * @internal  	     
     */ 	
    public function lock($uid)
    {   
        $this->clearLocks();
        $this->getDbConnection()->query('REPLACE INTO `lock` SET material_id='.$this->id.', type_id='.$this->objectDefinition->id.', user_id='.$uid.', dat=NOW()');    
    }
    
    /**    
     * @internal  	     
     */ 	
    public function unlock()
    {   
        $this->getDbConnection()->query('DELETE FROM `lock` WHERE material_id='.$this->id.' and type_id='.$this->objectDefinition->id);    
    }
    
    /**
     * Возвращает "Тип материалов" объекта 
     *         
     * @return int    
     */  
    public function getType()
    {
        return $this->objectDefinition->getId();
    }

    /**
     * Возвращает таблицу БД, в которой хранятся поля объекта
     *         
     * @return string    
     */  
    public function getTable()
    {
        return $this->objectDefinition->getTable();
    }
    
    /**
     * Возвращает описание полей объекта
     *         
     * @return array    
     */ 
    public function getFieldsDef()
    {
		if (!$this->objectDefinition) throw new \Exception('no objectDefinition');
        return $this->objectDefinition->getFields();
    }

    /**
     * Возвращает прочитанное из БД поле объекта в соответствии с типом поля
     * 
	 * @internal
     * @param string $name имя поля             
     * @return mixed    
     */   
    public function getDynamicField($name)
    {                              
        $fields = $this->getFieldsDef();
        
        if (!isset($fields[$name])) {
            if (isset($this->fields[$name]))
                return $this->fields[$name];
            return false;
        }  
            //throw new LogicException('Property "'.$name.'" does not exist.'); 
    
        switch($fields[$name]['type']){
        	//case FIELD_FORM: 
            //    return $this->getFormField($fields[$name]);
        	//	break;
        	case FIELD_LINK: 
        	case FIELD_MATERIAL: 
                return $this->getMaterialField($fields[$name]);
        		    break;
        	//case FIELD_HLINK: 
          //      return $this->getHlinkField($fields[$name]);
        	//	    break;
        	case FIELD_LINKSET: 
        	case FIELD_MATSET: 
                return $this->getLinksetField($fields[$name]);
        		    break;
        	default: 
                return $this->getPlainField($fields[$name]); 
        }
    }
    
	/** @internal */
    public function offsetExists ( $offset )
    { 
    
        return $this->__isset( $offset );
    
    }
    
	/** @internal */
    public function offsetGet ( $offset )
    {
    
        return $this->__get( $offset );
    
    }
    
	/** @internal */
    public function offsetSet ( $offset , $value ) {}
    
	/** @internal */
    public function offsetUnset ( $offset ) {}

    /**
     * Чтение из БД "простого" поля: текст, число, логическое и т.д.
     * 
	 * @internal
     * @param string $name имя поля             
     * @return mixed    
     */   
    private function getPlainField($field)
    {
        if (!isset($this->fields[$field['name']])) {                     
			$this->fields[$field['name']] = $this->getDbConnection()->fetchColumn('SELECT `'.$field['name'].'` FROM '.$this->table.' WHERE id='.(int)$this->fields['id']);
		}
        return $this->fields[$field['name']];
    }

    /**
     * Чтение из БД группы материалов, связанных с полем
     * 
	 * @internal
     * @param string $name имя поля             
     * @return Iterator\Object    
     */    
	private function getLinksetField($field)
	{					
        if (!isset($this->fields[$field['name']])) {
			$this->fields[$field['name']] =  new Iterator\Linkset( $this, $field );
			$this->fields[$field['name']]->orderBy('b.tag','ASC');
		}
        
		return $this->fields[$field['name']];   
    }
    	 
    /**
     * Чтение из БД группы материала, связанных с полем
     * 
	   * @internal
     * @param string $name имя поля             
     * @return DynamicFieldsObject    
     */     
    private function getMaterialField($field)
    {
        if (isset($this->fields[$field['name']]) && is_object($this->fields[$field['name']]))
            return $this->fields[$field['name']];
            
        $slot = new Cache\Slot\MaterialField($this->table, $this->fields['id'], $field['name']);
        if (false === ($_tmp = $slot->load())) {        
    
            if (!isset($this->fields[$field['name']]))
                $this->fields[$field['name']] = $this->getPlainField($field);

            if ($this->fields[$field['name']]) {
				
				if ($field['pseudo_type'] == PSEUDO_FIELD_LINK_CATALOG) {
                     $this->fields[$field['name']] = Catalog::getById($this->fields[$field['name']]);
                }
				elseif ($field['pseudo_type'] == PSEUDO_FIELD_LINK_USER) {                
                     $this->fields[$field['name']] = User::getById($this->fields[$field['name']]);                
                } 
				else {
                                
                    if ($field['type'] == FIELD_LINK) {
                        if ($field['len']) {
                            $c = Catalog::getById($field['len']);
                            if (!$c) throw new Exception\CMS('Catalog '.$field['len'].' is not found.');
                            $type = $c->materialsType;
                        } else $type = $this->objectDefinition->id;
                    } else {
                        $type = $field['len'];
                    }
                    
					try {
						$this->fields[$field['name']] = self::getByIdType( $this->fields[$field['name']], $type );
					} catch (\Exception $e) {
						$this->fields[$field['name']] = null;
					}
                
                }
            
            } else {
                
                $this->fields[$field['name']] = null;
                
            }
            
            $slot->addTag(new Cache\Tag\Material($this->table, $this->fields['id']));
            $slot->save($this->fields[$field['name']]);
        } else {
            $this->fields[$field['name']] = $_tmp;
        }
        
        return $this->fields[$field['name']];
    }
    
    /**
     * Чтение из БД поля-ссылки
     * 
	 * @internal
     * @param string $name имя поля             
     * @return array    
     */    
    private function getHlinkField($field)
    {
        if (isset($this->fields[$field['name']]) && is_array($this->fields[$field['name']]))
            return $this->fields[$field['name']];
                      
        $slot = new Cache\Slot\MaterialField($this->table, $this->fields['id'], $field['name']);
        if (false === ($a = $slot->load())) {  
            
            if (!isset($this->fields[$field['name']]))
                $this->fields[$field['name']] = $this->get_plain_field($field);
                        
        	$a = $this->getDbConnection()->fetchAssoc('SELECT * FROM field_link WHERE link_id='.(int)$this->fields[$field['name']]);
            if ($a['structure_id']) {
                if ($a['structure_type'] == 'main') {
                    $c = Catalog::getById($a['structure_id']);
                    if ($c) {
                        $a['link_value'] = $c->url;
            			$a['name'] = $c->name;
        			}
                } else {
        			$b = $this->getDbConnection()->fetchAssoc('SELECT idcat, alias, name FROM '.$a['structure_type'].' WHERE id='.$a['structure_id']);
        			if ($b) {
        			    $c = Catalog::getById($b['idcat']);
        			    if ($c) $path = $c->url;
        				$a['link_value'] = $path.$b['alias'].'.html';
        				$a['alias'] = $b['alias'];
        				$a['idcat'] = $b['idcat'];
        				$a['name'] = $b['name'];
        			}
        		}
            }
            $this->fields[$field['name']] = $a;
        
            $slot->addTag(new Cache\Tag\Material($this->table, $this->fields['id']));
            $slot->save($this->fields[$field['name']]);
            
        } else {
            $this->fields[$field['name']] = $a;
        }
        
        return $this->fields[$field['name']];
    } 
    
    /**
	 * DEPRECATED
	 *
     * Чтение из БД объектов, на которые ссылается поле
     * 
     * При построении SQL запроса к базе данных, таблице, из которой производится чтение полей объектов   
     * присваивается псевдоним "A", а таблице, в которой хранятся связи между объектами - "B". Рекомендуется
     * использовать эти псевдонимы, если вы используете параметры $fields, $where, $order, $group, $limit               
     * 
	 * @deprecated
	 * @internal
     * @param string $fieldname имя поля     
     * @param string $fields поля, которые запрашивать из таблицы БД при выборке 
     * @param string $where параметр WHERE запроса     
     * @param string $order параметр ORDER BY запроса    
     * @param string $group параметр GROUP BY запроса    
     * @param string $limit параметр LIMIT запроса                                    
     * @return Iterator\Object    
     */  
    public function selectLinks($fieldname, $fields='A.*', $where='', $order='B.tag', $group='', $limit='')
    {
        $flds = $this->getFieldsDef();
        if (!isset($flds[$fieldname]))
            throw new Exception\CMS('Property "'.$fieldname.'" does not exist.'); 

        $field = $flds[$fieldname];
        
        if ($field['type'] != FIELD_LINKSET && $field['type'] != FIELD_MATSET) return false;
        
        if ($fields != '*') $fields = 'A.id,'.$fields;
        
        $w = PUBLISHED.' and ';       
        
        if ($field['type'] == FIELD_LINKSET) {
        
    	    if ($field['len'] == CATALOG_VIRTUAL_USERS) {
              $tableto = User::TABLE;
              $dtype = User::TYPE;
              $w = '';
          } elseif ($field['pseudo_type'] == PSEUDO_FIELD_CATOLOGS) {
              $tableto = Catalog::TABLE;
              $dtype = Catalog::TYPE;
              $w = '';          
          } elseif (!$field['len']) {
    	        $tableto = $this->table;
    	        $dtype = $this->objectDefinition->id;
    	    } else {
              $c = Catalog::getById($field['len']);
              if (!$c) throw new Exception\CMS('Catalog '.$field['len'].' is not found.');
              $dtype = $c->materialsObjectDefinition->id;
              $tableto = $c->materialsObjectDefinition->table;
          } 
          
        } else {
            
            $od = new ObjectDefinition($field['len']);
            $tableto = $od->table;
            $dtype = $field['len'];
        }
        
  	    $linktable = $this->table.'_'.$tableto.'_'.$field['name'];
  	    $sql = 'SELECT '.$fields.' FROM '.$tableto.' A LEFT JOIN '.$linktable.' B ON (A.id=B.dest) WHERE '.$w.' B.id='.$this->fields['id'];
        if ($where) $sql .= ' and ('.$where.')';
        if ($group) $sql .= ' GROUP BY '.$group;
        if ($order) $sql .= ' ORDER BY '.$order;
        if ($limit) $sql .= ' LIMIT '.$limit;
  
  	    $res = new Iterator\Object();
		$r = $this->getDbConnection()->query($sql);
  	    while ($f = $r->fetch()) {
            $res->append(DynamicFieldsObject::fetch($f, $dtype, $tableto));
        }
        return $res;
    }
    
    /**
     * Чтение из БД объектов, которые связаны полем $field с объектом.
     * 
     * При построении SQL запроса к базе данных, таблице, из которой производится чтение полей объектов   
     * присваивается псевдоним "A", а таблице, в которой хранятся связи между объектами - "B". Рекомендуется
     * использовать эти псевдонимы, если вы используете параметры $fields, $where, $order, $group, $limit               
     * 
	 * @internal
     * @param int|string $from ID "Типа материалов" или имя таблицы БД, в которой хранятся объекты       
     * @param string $field имя поля по которому объекты связаны с текущим
     * @param string $fields поля, которые запрашивать из таблицы БД при выборке 
     * @param string $where параметр WHERE запроса     
     * @param string $order параметр ORDER BY запроса    
     * @param string $group параметр GROUP BY запроса    
     * @param string $limit параметр LIMIT запроса                                    
     * @return Iterator\Object    
     */ 
    public function selectLinksIn($from,$field,$fields='*',$where='',$order='',$group='',$limit='')
    {
        if (is_int($from)) {
            $type = $from;
            $tablefrom = ObjectDefinition::findById($type)->table;
        } else {
            $tablefrom = $from;
            $type = ObjectDefinition::findByTable($tablefrom)->id;
        }
        
        if ($fields != '*') $fields = 'A.id,'.$fields;
        
        $f = $this->getDbConnection()->fetchArray("select A.len, A.type from types_fields A, types B where B.id=A.id and B.id=$type and A.name='$field'");
        if (!$f)
            throw new Exception\CMS('Field '.$field.' is not found for type '.$type);

        if ($f[1] == FIELD_LINKSET) {
            if ($f[0]) {
            	$c = Catalog::getById($f[0]);
            	$tableto = $c->materialsTable;
            } else $tableto = $tablefrom;
        } elseif ($f[1] == FIELD_MATSET) {
            $tableto =  ObjectDefinition::findById($f[0])->table;
        } else { 
            return FALSE;
        }
        
        $linktable = $tablefrom.'_'.$tableto.'_'.$field;
        $sql = 'SELECT '.$fields.' FROM '.$tablefrom.' A LEFT JOIN '.$linktable.' B ON (A.id=B.id) WHERE '.PUBLISHED.' and B.dest='.$this->fields['id'];
        if ($where) $sql .= ' and '.$where;
        if ($group) $sql .= ' group by '.$group;
        if ($order) $sql .= ' order by '.$order;
        if ($limit) $sql .= ' limit '.$limit;

   	    $res = new Iterator\Object();
		$r = $this->getDbConnection()->query($sql);
	    while ($f = $r->fetch()) {
			$res->append(self::fetch($f, $type, $tablefrom));
		}
        return $res;
        
    }
    
    /**
     * Удаляет объект из БД
     *      
     * @return void     
     */  
    public function delete()
    {
        // удаление ссылок с удаляемого материала
        $r = $this->getDbConnection()->query("select B.name, B.len, B.type, B.pseudo_type from types A, types_fields B where A.alias='".$this->table."' and B.id=A.id and (B.type=".FIELD_LINKSET." or B.type=".FIELD_MATSET.")");
        while ($f = $r->fetch(\PDO::FETCH_NUM)) {
            $tbl = ObjectDefinition::get_table($f[2], $f[1], $this->objectDefinition->id, $f[3]);
            if ($f[2] != FIELD_LINKSET && $f[3] != PSEUDO_FIELD_TAGS) {
            	$r1 = $this->getDbConnection()->query("select dest from ".$this->table."_".$tbl."_".$f[0]." where id=".$this->id);
            	while ($f1 = $r1->fetch(\PDO::FETCH_NUM)) {
            	   $m = Material::getById($f1[0], $f[1], $tbl);
            	   $m->delete();
            	}
            }
            $this->getDbConnection()->executeQuery("delete from ".$this->table."_".$tbl."_"."$f[0] where id=".$this->id);
        }
        
        // удаление ссылок на этот материал
        if (property_exists($this, 'idcat') && $this->idcat >= 0) {
        	$r = $this->getDbConnection()->query("select A.alias, B.name, B.type from types A, types_fields B where B.id=A.id and B.len=".$this->idcat." and (B.type=".FIELD_LINK." or B.type=".FIELD_LINKSET.")");
        	while ($f = $r->fetch(\PDO::FETCH_NUM)) {
              if ($f[2] == FIELD_LINK) {
        	    $this->getDbConnection()->executeQuery("update $f[0] set $f[1]=0 where $f[1]=".$this->id);
        	  } else {
        	    $this->getDbConnection()->executeQuery("delete from $f[0]"."_".$this->table."_"."$f[1] where dest=".$this->id);
        	  }
        	}
        } else {
        	$r = $this->getDbConnection()->query("select A.alias, B.name from types A, types_fields B where B.id=A.id and B.len = ".$this->objectDefinition->id." and B.type=".FIELD_MATSET);
            while ($f = $r->fetch(\PDO::FETCH_NUM)) {
        	  $this->getDbConnection()->executeQuery("delete from $f[0]"."_".$this->table."_"."$f[1] where dest=".$this->id);
        	}
        }
                
        // удаление самого материала
        $this->getDbConnection()->executeQuery("delete from ".$this->table." where id=".$this->id);
        
    } 
    
/*
    private function get_form_field($field)
    {    
        if (isset($this->fields[$field['name']]) && is_object($this->fields[$field['name']]))
            return $this->fields[$field['name']];
            
        $slot = new Cache\Slot\MaterialField($this->table, $this->fields['id'], $field['name']);
        if (false === ($_tmp = $slot->load())) {   
                       
            if (!isset($this->fields[$field['name']])) 
                $this->fields[$field['name']] = $this->get_plain_field($field);
                
            $this->fields[$field['name']] = Form::byId($this->fields[$field['name']]);
        
            $slot->addTag(new Cache\Tag\Material($this->table, $this->fields['id']));
            $slot->save($this->fields[$field['name']]);
            
        } else {
            $this->fields[$field['name']] = $_tmp;
        }
        
        return $this->fields[$field['name']];
    }
*/    

    /**
     * Сохранение объекта
     *      
     * @return void     
     */  
    abstract public function save();
    
	/** @internal */
    protected function saveDynimicLinks()
    {
        $fields = $this->getFieldsDef();
        if (is_array($fields)) foreach ($fields as $name => $field)
		{
            $type = $field['type'];
            
            if ($type==FIELD_LINKSET || $type==FIELD_MATSET) $tbl = ObjectDefinition::get_table($type, $field['len'], $this->objectDefinition->id, $field['pseudo_type']);
            
            if (isset($this->fields[$name]))
			{
                if ($field['pseudo_type'] == PSEUDO_FIELD_TAGS)
				{
                      $this->process_tags($this->fields[$name], $this->table, $tbl, $name, $this->id, $type);
            	} 
				elseif (($type == FIELD_LINKSET)||($type==FIELD_MATSET))
				{
            	  	 $this->insert_links($this->fields[$name], $this->table, $tbl, $name, $this->id, $type, $field['len']);
					 if ($type==FIELD_MATSET) $this->confirm_added($tbl, Application::getInstance()->getUser()->id);
                }
            }
        } 
    }

	/** @internal */
    protected function saveDynamicFields($exclude = null, $hidden = true)
    {
        $fields = $this->getFieldsDef();
               
        $values = '';
        
        if (is_array($fields)) foreach ($fields as $name => $field) 
        {
        
            if (is_array($exclude) && in_array($name, $exclude)) continue;
        
            $type = $field['type'];
			
			if ($name == 'dat_update')
			{
				$values .= ',dat_update=NOW()';
				continue;
			}
			
			if ($name == 'dat' || $name == 'date_reg')
			{
        	    if ($this->fields[$name]) {
        		      $values .= ",`".$name."`='".$this->fields[$name]."'";
        		} else {
        		     if (!$this->id) $values .= ",`".$name."`=NOW()";
                }
				continue;
			}			
          
        	if ($field['shw'] || $hidden)
			{
        	
				if ($type != FIELD_LINKSET && $type != FIELD_MATSET) 
				{
						if (!isset($this->fields[$name])) continue;
				}
        
                if (/*$type==FIELD_LINKSET || */$type==FIELD_MATSET || $type==FIELD_MATERIAL)
				{
					$tbl = ObjectDefinition::get_table($type, $fields[$name]['len'], $this->objectDefinition->id);
				}
        
                if ($type == FIELD_MATSET || $type==FIELD_MATERIAL)
				{
					$this->confirm_added($tbl, Application::getInstance()->getUser()->id);
				}
        
				if ( $type==FIELD_MATERIAL ) {
					
					if ($value = json_decode($this->fields[$name], true) ) {
						if (isset($value['id'])) {
							$this->fields[$name] = $value['id'];
						}
					}
					
					if (!(int)$this->fields[$name]) {					
						$data = $this->getDbConnection()->fetchAll('SELECT `'.$name.'` as fld FROM '.$this->table.' WHERE idcat < 0 and id='.$this->id);
						foreach ($data as $f1) { 
							 if ($f1['fld']) {
								 $m = Material::getById($f1['fld'], 0, $tbl);
								 $m->delete();							 
							 }
						}	
					}
				}
		
                if ($type == FIELD_LONGTEXT) 
				{
            
        			  $this->fields[$name] = $this->process_longtext($this->fields[$name]);
        			  $values .= ',`'.$name.'`='.$this->getDbConnection()->quote($this->fields[$name]);
        	       		  
                } 
				elseif ($type==FIELD_INTEGER || $type==FIELD_LINK || $type==FIELD_FORM || $type==FIELD_MATERIAL)
				{ 
        		    $values .= ",`".$name."`='".(int)$this->fields[$name]."'";         
        	    } 
				elseif ($type==FIELD_DOUBLE)
				{ 
        		    $values .= ",`".$name."`=".(double)$this->fields[$name];                 			
        	    } 
				elseif (($type == FIELD_LINKSET)||($type==FIELD_MATSET))
				{
        	          			
        	    }
		    //elseif ($type == FIELD_HLINK) {
        	    //     $values .= ",`".$name."`='".(int)$this->process_hlink($this->fields[$name])."'";         
        	    //} 
				elseif ($type == FIELD_DATETIME) {
        	        if (!$this->fields[$name])
        		        $values .= ",`".$name."`=NULL";
        	    	    else $values .= ",`".$name."`='".$this->fields[$name]."'"; 
        		
        	    } 
				else { 
        	        $values .= ',`'.$name.'`='.$this->getDbConnection()->quote($this->fields[$name]); 
        	    }
           }
        }
        return $values;
    }
	
	/**
	 * @internal
	 * @ignore
	*/
	private function confirm_added($tbl, $user_id) {		
		$not_added = ~ MATH_ADDED;
		$r1 = $this->getDbConnection()->query("select id from $tbl where autor=".(int)$user_id." and type & ".MATH_DELETED." > 0");
		while ($f1 = $r1->fetch(\PDO::FETCH_NUM))
		{ 
			 $m = Material::getById($f1[0], 0, $tbl);
			 $m->delete();
		}
		$this->getDbConnection()->executeQuery("update $tbl set type = type&$not_added|".MATH_PUBLISHED." where autor=".(int)$user_id." and type & ".MATH_ADDED." > 0");
	}

	/**
	 * @internal
	*/
	private function process_hlink($value) {
		$hl = explode('||', $value);
		if ($hl[0]) {
			$sql = 'UPDATE field_link SET link_type='.(int)$hl[1].', link_value="'.$hl[2].'", structure_type="'.$hl[3].'", structure_id='.(int)$hl[4].' WHERE link_id='.(int)$hl[0];
			$this->getDbConnection()->executeQuery($sql);	    
		} else {
			$sql = 'INSERT INTO field_link (link_type,link_value,structure_type,structure_id) VALUES ('.(int)$hl[1].',"'.$hl[2].'","'.$hl[3].'",'.(int)$hl[4].')';
			$this->getDbConnection()->executeQuery($sql);
			$hl[0] = $this->getDbConnection()->lastInsertId();
		}
		return $hl[0];
	}	
	
	/**
	 * @internal
	*/
	private function insert_links($values, $math, $tbl, $name, $id2, $type, $type2) {
			
		if ($type == FIELD_MATSET) {
			$r = $this->getDbConnection()->query("SELECT dest FROM ".$math."_".$tbl."_".$name." WHERE id=$id2");
			$old = array();
			while ($f = $r->fetch()) {
				$old[ $f['dest'] ] = $f['dest'];
			}
		}
		
		$this->getDbConnection()->executeQuery("delete from ".$math."_".$tbl."_".$name." where id=$id2"); 
		$link_list = json_decode($values);
		if (is_array($link_list)) {
			while (list ($no, $link) = each ($link_list)) {
		  
			  if (is_object($link)) {
				  if ($type == FIELD_LINKSET) {
					  $link = $link->id;             
				  }
				  else {
					  $link = (array)$link;
					  $link['catalog_id'] = -1;
					  $link['alias'] = 'hidden';
					  if ($link['id']) {
						  $m = DynamicFieldsObject::getByIdType($link['id'], $type2);
						  $m->fields = $link;
					  } else {
						  $m = DynamicFieldsObject::fetch($link, $type2, $tbl);
					  }
					  $m->save();
					  $link = $m->id;
				  }
			  }
			  
			  if ((int)$link) { 
				  if (isset($old[ $link ])) unset($old[ $link ]);
				  $this->getDbConnection()->executeQuery("insert into ".$math."_".$tbl."_".$name." (id,dest,tag) values (".$id2.",".(int)$link.",".$no.")");
				  if ($type == FIELD_MATSET) $this->getDbConnection()->executeQuery("update $tbl set tag=$no where id=$link"); 
			  }
			  
			}
		}
		
		if ($type == FIELD_MATSET) {
			foreach ($old as $o) try {			
				$m = DynamicFieldsObject::getByIdType($o, $type2);
				$m->delete();
			} catch (\Exception $e) {}
		}
	}	
	
	/**
	 * @internal
	*/
	private function process_longtext($value) {
		$value = str_replace('quote;','"',$value);
		$value = str_replace('src="http://'.getenv('HTTP_HOST'),'src="',$value);
		$value = str_replace('lowsrc=http://'.getenv('HTTP_HOST'),'lowsrc=',$value);
		$value = str_replace('lowsrc=""','',$value);
		return $value;
	}

	/**
	 * @internal
	*/
	private function process_tags($value, $math, $tbl, $name, $id2, $type) {
		$ltable = $math."_".$tbl."_".$name;
		$this->getDbConnection()->executeQuery("delete from ".$ltable." where id=".(int)$id2); 
		$tags = explode(",",$value);
		$i = 1;
		foreach ($tags as $tag) {
			$tag = trim($tag);
			if ($tag) {
				$id = $this->getDbConnection()->fetchColumn('SELECT id FROM '.$tbl.' WHERE name=?', array($tag));
				if (!$id) {
					$this->getDbConnection()->executeQuery('INSERT INTO '.$tbl.' SET name='.$this->getDbConnection()->quote($tag).', alias="tag", type=3, autor=1, tag=0, dat=NOW(), dat_update=NOW(), idcat='.CATALOG_VIRTUAL_HIDDEN);
					$id = $this->getDbConnection()->lastInsertId();
				}
				$this->getDbConnection()->executeQuery('INSERT INTO '.$ltable.' (id,dest,tag) values ('.(int)$id2.','.(int)$id.','.$i++.')');
			}
		}
	}	

}