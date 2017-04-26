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
 * Раздел сайта
 *
 * @property string $name название раздела
 * @property string $alias псевдоним раздела
 * @property string $url абсолютный url раздела
 * @property Iterator\Catalog\Children $children дочерние разделы
 *
 * @package CeteraCMS
 * @api
 **/ 
class Catalog extends DynamicFieldsObjectPredefined implements SiteItem {
    
	/** 
	 * ID типа материалов для разделов
	 */
    const TYPE = 4;

    /** 
	 * Таблица, в которой хранится информация о разделах
	 */
    const TABLE = 'dir_data';

    /** @internal Раздел наследует разрешения от родительского раздела */         
    const INHERIT               = 2;    
    /** @internal Раздел является ссылкой на другой раздел */      
    const LINKED                = 16;    
    /** @internal В материалах раздела автоматически заполняется Алиас */  
    const AUTOALIAS             = 32;     
    /** @internal Алиас материалов раздела строится на основе транслитерации заголовка */  
    const AUTOALIAS_TRANSLIT    = 128;
	/** @internal Алиас материалов раздела строится на основе ID */  
	const AUTOALIAS_ID          = 256;
	   
    /** @internal Дочерние разделы */ 
    private $_children = FALSE;
    
    /**
     * Порядковый номер раздела
     * @internal          
     * @var int
     */   
    protected $_tag;
    
    /** 
     * Родительский раздел
     * @internal 
     * @var Catalog          
     */ 
    protected $_parent = FALSE;
    
    /** 
     * Путь до раздела
     * @internal 
     * @var Iterator\Catalog         
     */ 
    protected $_path = FALSE;
    
    /**
     * Имя раздела
     * @internal          
     * @var string
     */   
    protected $_name;
    
    /**
     * Алиас раздела
     * @internal          
     * @var string
     */   
    protected $_alias;
    
    /**
     * Свойства раздела, битовое поле
     * @see Catalog::INHERIT, Catalog::LINKED, Catalog::AUTOALIAS, Catalog::AUTOALIAS_TRANSLIT, Server::DEFAULT_SERVER           
     * @internal          
     * @var int
     */ 
    protected $_catalogType;
        
    /**
     * Шаблон, который запускается при обращении к разделу
     * @internal          
     * @var string
     */ 
    protected $_template;
    
    /**
     * Тип материалов раздела
     * @internal          
     * @var int
     */ 
    protected $_materialsType;
    
    /**
     * Дата создания раздела
     * @internal          
     * @var string
     */ 
    protected $_dat;
       
    /**
     * URL для предпросмотра материалов раздела
     * @internal          
     * @var string
     */ 
    protected $_preview;
    
    /**
     * Абсолютный URL раздела. Без имени сайта
     * @internal          
     * @var string
     */ 
    protected $_url = FALSE;
    
    /**
     * Полный URL раздела
     * @internal          
     * @var string
     */ 
    protected $_fullUrl = FALSE;
    
    /**
     * Путь до раздела для Ext.tree (/root/item-0/.../item-ID)
     * @internal          
     * @var string
     */ 
    protected $_treePath = FALSE;
    
    /**
     * Родительский сервер
     * @internal          
     * @var Server
     */ 
    protected $_parentServer = FALSE;
    
    /**
     * Раздел скрыт
     * @internal          
     * @var bool
     */ 
    protected $_hidden;
    
    /**
     * Шаблон "по умолчанию" для раздела
     * @internal          
     * @var string
     */   
    protected $_defaultTemplate = FALSE;
    
    /**
     * Таблица, в которой хранятся материалы раздела
     * @internal         
     * @var string
     */   
    protected $_materialsTable = null;
    
	/** @internal */
    protected $_materialsObjectDefinition = null;
    
    /**
     * Если раздел является ссылкой, то раздел, на который указывает ссылка. 
     * Иначе $this  
     * @internal
     * @var Catalog          
     */   
    protected $_prototype = FALSE;   
    
    /**
     * Наследуется ли видипость и порядок полей у материалов 
     * @internal
     * @var boolean          
     */   
    protected $_inheritFields = TRUE;         
       
    /**
     * Устанавливает поля раздела
     *  
	 * @internal
     * @param array $fields поля объекта            
     * @return void
     */   
    public function setFields($fields) 
    {
		    if (!isset($fields['alias']) && isset($fields['tablename'])) {
            $fields['alias'] = $fields['tablename']; 
            unset($fields['tablename']);
        }

        if (!isset($fields['materialsType']) && isset($fields['typ'])) {
    		    $fields['materialsType'] = $fields['typ']; 
            unset($fields['typ']); 
        }
    
        if ($fields['type']&Catalog::LINKED) {
			try {
				$fields['prototype'] = Catalog::getById($fields['materialsType']);
				$fields['materialsType']  = $fields['prototype']->materialsType;
			} catch (\Exception $e) {
				$fields['prototype'] = Catalog::getRoot();
			}
        } else {
            $fields['prototype']    = $this;
        }
        
        parent::setFields($fields);
		$this->_catalogType   = $fields['type'];
        $this->_inheritFields = isset($fields['inheritFields'])?$fields['inheritFields']:false;
    }
	   
     
	
  	/**
  	 * Возвращает раздел по его идентификатору.	
  	 * 	 
	 * @api
  	 * @param int $id ID раздела 	 
  	 * @return Catalog
  	 * @throws Exception\CMS	 
  	 */     	
  	public static function getById($id) 
    {
        if ($id == 0) return static::getRoot();
        return parent::getById($id);
  	}
	
	/**
	 * Возвращает корневой раздел
	 *  
     * @api	 
	 * @return Catalog
	 */ 
    public static function getRoot()
    {
        $c = static::create();
		$c->setFields(array(
            'id'            => 0,
            'name'          => 'root', 
            'type'          => 0,
            'tag'           => 0,
            'alias'         => '',
            'template'      => '',
            'materialsType' => 0,
            'dat'           => '',
            'preview'       => '',
            'template'      => ''
        ));
        return $c;
    }
    
	/** 
	 * @internal
	 */
    public static function fetch($data, $i_am_server = false)
    {
        if ($i_am_server) return parent::fetch($data);
        
        if (is_array($data)) {
            if ($data['is_server']) {
                return Server::fetch($data, true);
            } else {
                return parent::fetch($data);
            }
        } else {
            $fields = self::getDbConnection()->fetchAssoc('SELECT A.*, B.level FROM `'.self::TABLE.'` A LEFT JOIN dir_structure B ON (A.id=B.data_id) WHERE A.id = ?', array($data));
            if ($fields) {
                return static::fetch($fields);
            } else {
                return parent::fetch($data);
            }
        }
    }    
       
    /**
     * Является ли раздел ссылкой на другой раздел  
     *   
     * @api     
     * @return boolean
     */   
    public function isLink()
    {
        return $this->_catalogType&Catalog::LINKED?true:false;
    }
    
    /**
     * Является ли раздел сервером
     * 
     * @api	 
     * @return boolean
     */   
    public function isServer()
    {
        return FALSE;
    }
    
    /**
     * Является ли раздел скрытым
     *    
     * @api     
     * @return boolean
     */   
    public function isHidden()
    {
        return $this->_hidden;
    }
    
    /**
     * Является ли раздел корневым
     *    
     * @api     
     * @return boolean
     */ 
    public function isRoot()
    {
        return $this->id == 0;
    }
    
    /**
     * Наследует ли раздел разрешения, заданные для родительского раздела 
     *   
     * @api	 
     * @return boolean
     */         
    public function isInheritsPermissions()
    {
        if ($this->isRoot()) return false;
        return $this->_catalogType&Catalog::INHERIT;
    }
    
    /**
     * Возвращает путь от корня до раздела
     *  
     * @api	 
     * @return Iterator\Catalog
     */    
    public function getPath()
    {      	
        if (!$this->_path) {
			
			$this->_path = new Iterator\Catalog\Path( $this );                
        
        }
        return $this->_path;
    }
       
    /**
     * Вычисляет путь до раздела
     *     
	 * @internal
     * @see getUrl(), getFullUrl(), getTreePath()        	 
     * @return void
     */         
    private function fillPath()
    {
          $this->_url = '';
          $this->_fullUrl = '';
          $this->_treePath = '';

          foreach ($this->getPath() as $item) { 	  
            $this->_treePath .= '/item-'.$item->id;
            if ($item->isRoot()) continue;
            
            $this->_fullUrl .= '/'.$item->alias;
            if ($item->isServer()) continue;
            
            $this->_url .= '/'.$item->alias;
          }
          $this->_url      = $this->_url.'/';
          $this->_fullUrl  = $this->_fullUrl.'/';
          $this->_treePath = '/root'.$this->_treePath;
          if ($this->_url == '') $this->_url = '/';
		  //die('***');
    }
    
    /**
     * Возвращает абсолютный URL раздела
     *  
     * @api	 
     * @return string
     */ 
    public function getUrl()
    {
        if ($this->_url === FALSE) $this->fillPath();
        return $this->_url;
    } 
    
    /**
     * Возвращает полный URL раздела (http://сервер/раздел1/.../разделN/)
     *    
     * @api	 
     * @param boolean $prefix добавлять http:// вначале 
     * @return string
     */ 
    public function getFullUrl($prefix = TRUE)
    {
        if ($this->_fullUrl === FALSE) $this->fillPath();
		
        if ($prefix) {
			if (!empty($_SERVER['HTTPS'] && $_SERVER['HTTPS'] !== 'off') || !empty($_SERVER['HTTP_HTTPS'] && $_SERVER['HTTP_HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
				$schema = 'https:/';
				else $schema = 'http:/';
			return $schema.$this->_fullUrl;
		}
        return $this->_fullUrl;
    }
    
	/** @internal */
    public function getBoUrl()
    {
        $path = $this->getFullUrl(false);
        if (strlen($path)>50) $path = substr($path,0,20).'...'.substr($path,-20);
        return  '<a title="'.$this->name.'" href="javascript:Cetera.getApplication().openBoLink(\'catalog:'.$this->getTreePath().'\')">'.
                $path.
                '</a>';
    }
    
    /**
     * Возвращает полный путь до раздела для Ext.tree
     *           
     * @return string
     */       
    public function getTreePath()
    {
        if ($this->_treePath === FALSE) $this->fillPath();
	    return $this->_treePath;
    }
    
    /**
     * Возвращает дочерние разделы
     *         
     * @api	 
     * @return Iterator\Catalog
     */ 
    public function getChildren() 
    {
        if (!$this->_children) { 

			$this->_children = new Iterator\Catalog\Children( $this );          
        
        }
        return $this->_children;
    }
    
    /**
     * Возвращает массив из идентификаторов раздела и дочерних разделов
     *     
     * @api	 
     * @return array
     */ 
    public function getSubs()
    {
        
        $r = fssql_query('
        	SELECT A.*, B.level 
        	FROM dir_data A, dir_structure B, dir_structure C
        	WHERE A.id=B.data_id and C.data_id='.$this->id.' and B.lft BETWEEN C.lft and C.rght');
        	
        while ($f = mysql_fetch_assoc($r)) {
            $c = Catalog::fetch($f);
            $result[] = $c->prototype->id;
        }
                    
        return $result;
    }
    
    /**
     * Возвращает родительский сервер
     *    
     * @api	 
     * @return Server
     */
    public function getParentServer()
    {
      if ($this->_parentServer) return $this->_parentServer;
      
      $fields = self::getDbConnection()->fetchAssoc( 
          " SELECT C.*
    		    FROM dir_structure B, dir_structure A, dir_data C
    		    WHERE A.data_id=? and B.lft<=A.lft and B.rght>=A.lft and
    		    B.data_id=C.id and C.is_server<>0", 
          array( (int)$this->id )
      );
      
      if ($fields) {
          $this->_parentServer = Server::fetch($fields);
          return $this->_parentServer;
      }
      return FALSE;
    } 
    
    /**
     * Возвращает родительский раздел
     *    
     * @api	 
     * @return Catalog
     */
    public function getParent()
    {
        if ($this->isRoot()) throw new Exception\CMS('No parent for root Catalog');
    
        if (!$this->_parent) {
        
            $fields = self::getDbConnection()->fetchAssoc( 
                " SELECT A.*
                  FROM dir_data A, dir_structure B, dir_structure C 
                  WHERE C.data_id=? and B.data_id=A.id and B.lft<C.lft and B.rght>C.rght and B.level=C.level-1", 
                array( (int)$this->id )
            );        
        
            if ($fields) {
                $this->_parent = Catalog::fetch($fields);
            } else {
                $this->_parent = Catalog::getRoot();
            }
        }
        return $this->_parent;
    }
       
    /**
     * Возвращает таблицу БД, в которой хранятся материалы раздела
     *           
     * @return string
     */
    public function getMaterialsTable()
    {
        return $this->materialsObjectDefinition->table; 
    }
    
    /**
     * Возвращает таблицу БД, в которой хранятся материалы раздела
     *           
     * @return string
     */
    public function getMaterialsObjectDefinition()
    {
        if (!$this->materialsType) return null;
    
        if (!$this->_materialsObjectDefinition)
            $this->_materialsObjectDefinition = new ObjectDefinition($this->materialsType);
        return $this->_materialsObjectDefinition; 
    }    
    
    /**
     * Ищет среди дочерних разделов раздел с заданным алиасом
     *    
     * @api	 
     * @param string @alias алиас раздела      
     * @return Catalog|FALSE  
     */     
    public function findChildByAlias($alias)
    {
        $alias = mysql_real_escape_string($alias);
        $r = fssql_query("select A.id from dir_data A, dir_structure B, dir_structure C where C.data_id=".$this->id." and B.data_id=A.id and B.lft BETWEEN C.lft and C.rght and A.tablename='$alias'");
        if (mysql_num_rows($r)) {
            return Catalog::getById(mysql_result($r,0));
        } else return FALSE;
    }    
     
    /**
     * Возвращает дочерний раздел с заданным алиасом
     *        
	 * @api
     * @param string $alias алиас раздела      
     * @return Catalog    
     */     
    public function getChildByAlias($alias)
    {
        $alias = mysql_real_escape_string($alias);
		
        //$slot = new Cache\Slot\CatalogByAlias($this->id, $alias);
        //if (false === ($f = $slot->load()) || !Application::getInstance()->isFrontOffice()) {
            $r = fssql_query("select A.* from dir_data A, dir_structure B, dir_structure C where C.data_id=".$this->id." and B.data_id=A.id and B.level=C.level+1 and B.lft BETWEEN C.lft and C.rght and A.tablename='$alias'");
            if (mysql_num_rows($r)) {
                $f = mysql_fetch_assoc($r);
                //$slot->addTag(new Cache\Tag\CatalogID($f['id']));
            } else {
				$f = null;
			}
            //$slot->addTag(new Cache\Tag\CatalogID($this->id));
            //$slot->save($cat);    
        //}
        if (!$f) throw new Exception\CMS(Exception\CMS::CAT_NOT_FOUND);
		
        $f['parent'] = $this;
        $cat = Catalog::fetch($f);		
		
        return $cat;
    }
    
    /**
     * Возвращает дочерний раздел по заданному пути
     *  
     * @api     
     * @param string|array $path путь, строка 'alias1/alias2/.../aliasN' или массив из алиасов          
     * @return Catalog
     * @throws Exception
     */      
    public function getChildByPath($path)
    {
        if (!is_array($path)) {
            $path = trim($path, " /");
            $pieces = explode("/", $path);
        } else {
             $pieces = $path;
        }
            
        if (!sizeof($pieces)) return $this;

        if ($alias = array_shift($pieces)) {

                $c = $this->getChildByAlias($alias);
                if (sizeof($pieces)) {
                    return $c->getChildByPath($pieces);
                } else {
                    return $c;
                }

        }
        return $this;
    }
        
    /**
     * Возвращает последний опубликованный материал раздела.
     *        
     * @api	 
     * @param string $fields список простых полей материала, которые выбирать из БД в конструктор материала     
     * @return Material  
     */ 
    public function getLastMaterial($fields = null, $subs = false)
    {
        $m = $this->getMaterials()->orderBy('main.dat', 'DESC')->setItemCountPerPage(1)->subfolders($subs);
        if ($fields) $m->select($fields);
        if (!count($m)) return false;
        return $m->current();
    }
       
    /**
     * Ищет материал c заданным алиасом
     *   
     * @api	 
     * @param string $alias алиас материала    
     * @param string $fields список простых полей материала, которые выбирать из БД в конструктор материала     
     * @return Material  
     * @throws Exception\CMS   
     */        
    public function getMaterialByAlias($alias, $fields = null)
    {
        $m = $this->getMaterials()->where('alias=:alias')->setParameter('alias', $alias);
        if ($fields) $m->select($fields);        
        if (!count($m)) throw new Exception\CMS(Exception\CMS::MATERIAL_NOT_FOUND);
        return $m->current();
    }
    
    /**
     * Ищет материал c заданным ID
     *    
     * @api	 
     * @param string $id ID материала       
     * @return Material  
     * @throws Exception\CMS   
     */        
    public function getMaterialByID($id)
    {
       return Material::getByID($id, $this->materialsType, $this->materialsTable);
    }
       
    /**
     * Возвращает материалы раздела     
     * 
     * @api	 
     * @return Iterator\Material    
     */       
    public function getMaterials()
    {
        // Для обратной совместимости
        if (func_num_args() > 0) return $this->_getMaterials(func_get_args());
    
        return new Iterator\Material($this);
    }
         
    /**
     * Возвращает php шаблон, исполняемый "по умолчанию" для раздела 
     *    
     * @api     
     * @return string       
     */  
    public function getDefaultTemplate() {
        if ($this->_defaultTemplate) return $this->_defaultTemplate;
 
        if (!$this->template)
            $this->_defaultTemplate = $this->parent->getDefaultTemplate();
            else $this->_defaultTemplate = $this->template;

        return $this->_defaultTemplate;
    }
    
    /**
     * Создает дочерний раздел
     *     
	 * @api
     * @param array $fields свойства нового раздела      
     * @return integer ID созданного раздела
     * @throws Exception\CMS         
     */  
    public function createChild($fields)
    {
    	if (!isset($fields['alias'])) throw new Exception\CMS(Exception\CMS::INVALID_PARAMS, 'alias');
    	if (!isset($fields['name'])) throw new Exception\CMS(Exception\CMS::INVALID_PARAMS, 'name');
    	if (!isset($fields['typ'])) throw new Exception\CMS(Exception\CMS::INVALID_PARAMS, 'typ'); //  тип материалов
    	
    	if (!isset($fields['server'])) $fields['server'] = 0;
    	
        try {
            $c = $this->getChildByAlias($fields['alias']);
        } catch (\Exception $e) {
            $c = false;
        }
    	if ($c) throw new Exception\CMS(Exception\CMS::CAT_EXISTS, $fields['alias']);
    	
    	if ($this->id == 0 && file_exists(DOCROOT.$fields['alias'])) 
            throw new Exception\CMS(Exception\CMS::CAT_PHYSICAL_EXISTS, $fields['alias']);
    	   	
      $type = 0;
    	
    	if ($fields['link']) {
          $type = $type | Catalog::LINKED;
          if (!$fields['name'] || !$fields['alias']) {
              $c = Catalog::getById((int)$fields['typ']);
              if (!$fields['name']) $fields['name'] = $c->name;
              if (!$fields['alias']) $fields['alias'] = $c->alias;
          }
      } else {
          $type = $type | Catalog::INHERIT;
      }
         
      if ($fields['autoalias']) {
          $type = $type | Catalog::AUTOALIAS | $fields['autoalias'];
          unset($fields['autoalias']);
      }
    	
      $r = fssql_query("SELECT MAX(A.tag)+1 
            	          FROM dir_data A, dir_structure B, dir_structure C
                        WHERE A.id=B.data_id and C.data_id=".$this->id." and B.lft BETWEEN C.lft and C.rght and B.level=C.level+1");
      $tag = 0;
      if (mysql_num_rows($r)) $tag = (int)mysql_result($r,0);
      if (!$tag) $tag = 1;
      
      $sql="INSERT INTO dir_data 
			SET tag=$tag,
				name='".mysql_escape_string($fields['name'])."',
				tablename='".mysql_escape_string($fields['alias'])."',
				type=$type,
				typ=".(int)$fields['typ'].",
				dat=NOW(),
				is_server=".(int)$fields['server'];
				
      if ((int)$fields['id']) {
          $sql .= ',id='.(int)$fields['id'];
          unset($fields['id']);
      }	  
				
      fssql_query($sql);
      $id = mysql_insert_id();
    	
    	$p = array();
    	foreach ($fields as $name => $value)
    		if ($name!='alias'&&$name!='server'&&$name!='name'&&$name!='typ'&&$name!='link')
    			$p[] = "$name='".addslashes($value)."'";
    	if (sizeof($p) && $id)
    		fssql_query('UPDATE dir_data SET '.implode(',', $p).' WHERE id='.$id);
    
        $tree = new CDBTree('dir_structure');
        $r = fssql_query('select id from dir_structure where data_id='.$this->id);
        $prnt = mysql_result($r,0);
        $tree->insert($prnt, array('data_id'=>$id));
        	   
        if ($fields['server']) {
        		fssql_query("delete from server_aliases where id=$id");
        		fssql_query("insert into server_aliases (id,name) values ($id,'".$fields['alias']."')");
        		$slot = new Cache\Slot\ServerByDomain($fields['alias']);
        		$slot->remove();
        }
        
        $tpl = new Cache\Tag\CatalogID(0);
        $tpl->clean();
        
        $tpl = new Cache\Tag\CatalogID($this->id);
        $tpl->clean();
        
    	return $id;

    }
    
    /**
     * Удаляет раздел
     *   
	 * @api
     * @return void   
     * @throws Exception\CMS         
     */         
    public function delete()
    {
        // нельзя удалить корневой раздел
        if ($this->id == 0) throw new Exception\CMS(Exception\CMS::NO_RIGHTS);
    
    	$tree = new CDBTree('dir_structure');
		
		$ids_arr = array($this->id);
		
		if (!$this->isLink()) {
    
			$r = fssql_query('SELECT A.data_id, A.level, C.tablename, D.alias 
							  FROM dir_structure A, dir_structure B, dir_data C, types D 
							  WHERE C.id=A.data_id and B.data_id='.$this->id.' and A.lft BETWEEN B.lft and B.rght and D.id=C.typ');
			
			while ($f = mysql_fetch_assoc($r))
			{
				try {
					$r1 = fssql_query('SELECT id FROM '.$f['alias'].' WHERE idcat='.$f['data_id']);
					while ($f1 = mysql_fetch_array($r1)) {
					   $m = Material::getById($f1[0], 0, $f['alias']);
					   $m->delete();
					}
				} catch (Exception $e) {}
				$ids_arr[] = $f['data_id'];
			}
		
		}
    
    	$parent = array();
    	$r = fssql_query('SELECT A.id, B.data_id 
                          FROM dir_structure A, dir_structure B 
                          WHERE A.data_id='.$this->id.' and B.lft<A.lft and B.rght>A.rght and B.level=A.level-1
            			  ORDER BY A.lft DESC');
    	$res = TRUE;
    	while($f = mysql_fetch_assoc($r))
		{
    		$parent[] = $f['data_id'];
    		if (!$tree->deleteAll($f['id'])) $res = FALSE;
    	} // while
		
    	if ($res && sizeof($ids_arr))
		{
    		$ids = implode(',',$ids_arr);
        
			fssql_query("DELETE FROM dir_data WHERE id IN ($ids)");
			fssql_query("DELETE FROM types_fields_catalogs WHERE catalog_id IN ($ids)");
        
    		$r = fssql_query("SELECT name FROM server_aliases WHERE id IN ($ids)");
    		while($f = mysql_fetch_assoc($r))
			{
        		$slot = new Cache\Slot\ServerByDomain($f['name']);
        		$slot->remove();	
    		}
        
    		fssql_query("DELETE FROM server_aliases where id IN ($ids)");
			fssql_query("DELETE FROM theme_config where server_id IN ($ids)");
        
    		foreach ($ids_arr as $id1) {
        		fssql_query("DELETE FROM users_groups_allow_cat where catalog_id=$id1");
                $tpl = new Cache\Tag\CatalogID($id1);
                $tpl->clean();
    		}
			
			// удаляем разделы-ссылки, ссылающиеся на раздел
			$r = fssql_query('SELECT id FROM dir_data WHERE type&'.Catalog::LINKED.'>0 and typ IN ('.$ids.')'); 
			while ($f = mysql_fetch_assoc($r)) {
				$c = Catalog::getById($f['id']);
				$c->delete();
			}			
    	}
    	
    	$this->updateCache();
    }
       
    /**
     * Приводит в порядок порядковые номера материалов раздела: удаляет дубликаты, дыры в нумерации.               
     *        
     * @internal	 
     * @return void
     */         
    public function fixMaterialTags() {
    	$r = fssql_query('SELECT count(tag) as cnt from '.$this->materialsTable.' where idcat='.$this->prototype->id.' group by tag having cnt>1');
    	if (!mysql_num_rows($r)) return;
    	$i = 100;
    	$r = fssql_query('SELECT id FROM '.$this->materialsTable.' WHERE idcat='.$this->prototype->id.' ORDER BY tag');
    	while($f = mysql_fetch_assoc($r)) 
		{
            fssql_query('UPDATE '.$this->materialsTable.' SET tag='.$i.' WHERE id='.$f['id']);
			$i = $i + 100;
		}
    }
    
    /**
     * Проверяет имеет ли пользователь или группа разрешение для данного раздела               
     *  
     * @param int $permission код разрешения  
     * @param int|array|User $groups id группы, массив id групп или пользователь                     
     * @return bool
     * @see PERM_CAT_OWN_MAT, PERM_CAT_ALL_MAT, PERM_CAT_ADMIN, PERM_CAT_VIEW, PERM_CAT_MAT_PUB     
     */ 
    public function allowAccess($permission, $groups)
    {
        if ($this->isInheritsPermissions()) {
            return $this->parent->allowAccess($permission, $groups);
        } else {
            if (!is_array($groups)) {
                if (is_object($groups))
                    $groups = $groups->groups;
                    else $groups = array($groups);
            }
            $r = fssql_query('SELECT COUNT(*) FROM users_groups_allow_cat WHERE permission='.(int)$permission.' and catalog_id='.(int)$this->id.' and group_id IN ('.implode(',',$groups).')');
            if ($r && mysql_num_rows($r) && mysql_result($r,0)>0) return TRUE;
            return FALSE;
        }
    }
    
    /**
     * Копирует раздел     
     *     
	 * @api
     * @param int $dest ID раздела, куда копировать
     * @param bool $subs копировать подразделы 
     * @param bool $materials копировать материалы           
     * @return int ID копии
     * @throws Exception     
     */  
    public function copy($dest, $subs = false, $materials = false)
    {      	   
    	$dest_obj = Catalog::getById($dest);
    	   
    	$alias = $this->alias;
    	$number = '';
    	   
    	$c = 1;
    	while ($c) {
            try {
                $c = $dest_obj->getChildByAlias($alias);
            } catch (\Exception $e) {
                $c = false;
            }
            if ($c) {
                $alias = $this->alias.'_copy'.$number;
                $number++;
            }
        }
        
    
    	$r = fssql_query("SELECT MAX(A.tag)+1, C.id 
    	          FROM dir_structure C LEFT JOIN dir_structure B ON (B.lft BETWEEN C.lft and C.rght and B.level=C.level+1) LEFT JOIN dir_data A ON (A.id=B.data_id)
                  WHERE C.data_id=$dest
    			  GROUP BY C.id");
    	if (!mysql_num_rows($r)) throw new Exception\CMS('Dest catalog is not found');
    	list($tag, $sid) = mysql_fetch_row($r);

        $fields = array(
            'tag' => $tag, 
            'tablename' => $alias
        );
        
        // Если сервер копируется внутрь другого сервера, то копия должна стать простым разделом
        if ($this->isServer() && !$dest_obj->isRoot())
            $fields['is_server'] = 0;

    	$new_id = Util::copyRecord('dir_data', 'id', $this->id, $fields);
    	if (!$new_id) throw new Exception\CMS('Error copying data record');

    	$tree = new CDBTree('dir_structure');
    	$new_sid = $tree->insert($sid, array('data_id'=>$new_id));
    	if (!$new_sid) {
    	    fssql_query("DELETE FROM dir_data WHERE id=$new_id");
    		throw new Exception\CMS('Error while creating new tree entry');
    	}
    	
    	$r = fssql_query('SELECT * FROM users_groups_allow_cat WHERE catalog_id='.$this->id);
    	while ($f = mysql_fetch_assoc($r))
    		fssql_query('INSERT INTO users_groups_allow_cat (catalog_id,group_id,permission) VALUES ('.$new_sid.','.$f['group_id'].','.$f['permission'].')');

    	// Копирование материалов
        if ($materials) {
            foreach ($this->getMaterials() as $material)
                $material->copy($new_id);
    	}

    	// Копирование подразделов
        if ($subs)
            foreach ($this->getChildren() as $child)
                $child->copy($new_id, $subs, $materials);
    	
        $tpl = new Cache\Tag\CatalogID(0);
        $tpl->clean();
    	
    	return $new_id;

    }
    
    /**
     * Перемещает раздел     
     *  
	 * @api	 
     * @param int $dest ID раздела, куда перемещать       
     * @return void  
     */  
    public function move($dest)
    { 
		$tree = new CDBTree('dir_structure');
		$r = fssql_query('select id from dir_structure where data_id='.$dest);
        $prntid = mysql_result($r,0);
        $r = fssql_query('select id from dir_structure where data_id='.$this->id);
        $strid = mysql_result($r,0);
		$tree->moveAll($strid, $prntid); 
		
        $this->_url = false;
        $this->_fullUrl = false;
        $this->_treePath = false;
        $this->_path = false;
         
        $this->updateCache();
    }
    
    /**
     * Очистить все кэши связанные с этим разделом     
     *          
     * @return void  
     */ 
    protected function updateCache()
    {
        $tpl = new Cache\Tag\CatalogID($this->id);
        $tpl->clean();
        
        $tpl = new Cache\Tag\CatalogID($this->parent->id);
        $tpl->clean();
        
        $tpl = new Cache\Tag\CatalogID(0);
        $tpl->clean();
    }
    
    /**
     * Измененить права доступа к разделу     
     *     
     * @param array $permissions новые права доступа       
     * @return void    
     */  
    public function updatePermissions($permissions)
    {
        if ($this->isLink()) return;
        
        fssql_query('DELETE FROM users_groups_allow_cat WHERE catalog_id='.$this->id);
        if (is_array($permissions)) 
            foreach ($permissions as $pid => $groups)
                foreach($groups as $gid) if ($gid && $gid != GROUP_ADMIN)
                    fssql_query('INSERT INTO users_groups_allow_cat SET permission='.(int)$pid.', group_id='.(int)$gid.', catalog_id='.(int)$this->id);

    }
    
    /**
     * Измененить видимость полей материалов в данном разделе     
     *    
	 * @internal
     * @param array $fields инфа о полях       
     * @return void    
     */  
    private function updateFields($fields)
    {
        if ($this->isLink()) return;
        
        fssql_query('DELETE FROM types_fields_catalogs WHERE catalog_id='.$this->id);
        if (is_array($fields)) 
            foreach ($fields as $fid => $data) {
                if ($data['force_show'] || $data['force_hide']) 
					fssql_query('INSERT INTO types_fields_catalogs SET catalog_id='.$this->id.', type_id='.$this->materialsType.', field_id='.$fid.', force_show='.(int)$data['force_show'].', force_hide='.(int)$data['force_hide']);
            }
    }    
       
    /** 
	 * @internal 
     */  	   
	public function fixTags()
	{
		$i = 1;
		foreach($this->children as $child)
			fssql_query('UPDATE dir_data SET tag='.$i++.' WHERE id='.$child->id);	
	}

    /**
     * Подвинуть раздел вверх или вниз на позицию    
     *  
	 * @api	 
     * @param bool $up двигать вверх, иначе вниз       
     * @return void       
     */  
    public function shift($up)
    {
		  $this->parent->fixTags();
    	$sign = $up?'<':'>';
    	$order = $up?'desc':'asc';
    
    	$r = fssql_query("select D.data_id as parentid, A.tag
                 FROM dir_data A, dir_structure B, dir_structure D
                 WHERE (A.id=".$this->id.") and B.data_id=A.id and D.lft<B.lft and D.rght>B.rght and D.level=B.level-1");
    	list($parent, $tag) = mysql_fetch_array($r);
    	
    	$r = fssql_query("SELECT A.tag, A.id FROM dir_data A, dir_structure B, dir_structure C
    	        WHERE A.id=B.data_id and C.data_id=$parent and B.lft BETWEEN C.lft and C.rght and B.level=C.level+1 and A.tag $sign $tag and A.id <> '.$this->id.' ORDER BY A.tag $order LIMIT 1");
    	if ($f = mysql_fetch_row($r)) {
    		fssql_query("update dir_data set tag=$f[0] where id=".$this->id);
    		fssql_query("update dir_data set tag=$tag where id=$f[1]");
    	}
    	
        $tpl = new Cache\Tag\CatalogID(0);
        $tpl->clean();
    }
    
    	/**
    	 * Проверяет, является ли раздел частью пути к текущему разделу в FO 	 
    	 *        
		 * @internal
    	 * @param Catalog $catalog раздел для проверки			 
    	 * @return bool
    	 */ 
    	public function inAppPath()
    	{
	   $app = Application::getInstance();
	   if (!$app->isFrontOffice()) return false;
	   return $app->getCatalog()->getPath()->has($this);
    }
    
    /**
     * Изменение свойств раздела и сохранение    
     *     
	 * @api
     * @param array $props новые свойства       
     * @return void   
     * @throws Exception      
     */  
    public function update($props)
    {
    
        if (isset($props['permissions'])) {
            $this->updatePermissions($props['permissions']);
            unset($props['permissions']);
        }
        
        if (isset($props['fields'])) {
            $this->updateFields($props['fields']);
            unset($props['fields']);
        }        
    
        $this->fields = $props;        
        $this->save();       
    }
    
	/**
	 * @internal
	 */
    public function getDynamicField($name)
    {                              
        if ($this->isLink())
            return $this->prototype->getDynamicField($name);
            else return parent::getDynamicField($name);
    } 
    
	/**
	 * Создать новый материал в разделе
	 * 
	 * @api
	 * @return \Cetera\Material
	 */	
    public function createMaterial()
    {
        $m = Material::factory( $this->materialsType, $this->materialsTable );
        $m->idcat = $this->id;
        return $m;
    }           
    
    /**
     * Сохранить раздел
     * 
     * @api     
     * @return void     
     */  	 
    public function save()
    {
        if ($this->isRoot()) return;
        
        if (isset($this->fields['alias']) && $this->fields['alias'] != $this->alias) {
            try {
                $c = $this->parent->getChildByAlias($this->fields['alias']);
            } catch (\Exception $e) {
                $c = false;
            }
            if ($c && $c->id != $this->id) throw new Exception\CMS(Exception\CMS::CAT_EXISTS);
            
            if ($this->parent->isRoot() && file_exists(DOCROOT.$this->fields['alias'])) 
                throw new Exception\CMS(Exception\CMS::CAT_PHYSICAL_EXISTS, $this->fields['alias']);
        }
        
        
        $set = '';
        if (isset($this->fields['name'])) $set .= ', name="'.mysql_escape_string($this->fields['name']).'"';
        if (isset($this->fields['alias'])) $set .= ', tablename="'.mysql_escape_string($this->fields['alias']).'"';
        if (isset($this->fields['template'])) $set .= ', template="'.mysql_escape_string($this->fields['template']).'"';
        if (isset($this->fields['typ'])) $set .= ', typ='.(int)$this->fields['typ']; 
        if (isset($this->fields['hidden'])) $set .= ', hidden='.(int)$this->fields['hidden'];  

        $type = 'type';
        
        if (!$this->isLink()) {
        
            if (isset($this->fields['inheritFields'])) $set .= ', inheritFields='.(int)$this->fields['inheritFields'];         
        
            $set .= $this->saveDynamicFields();
            $this->saveDynimicLinks();
        
            if (isset($this->fields['autoalias'])) {
                if ($this->fields['autoalias'])
                    $type = '('.$type.')|'.Catalog::AUTOALIAS;
                    else $type = '('.$type.')&'.~Catalog::AUTOALIAS;
            }
            
            if (isset($this->fields['autoalias_type'])) {
				if ($this->fields['autoalias_type'] == 3) $type = '(('.$type.')|'.Catalog::AUTOALIAS_ID.')&'.~Catalog::AUTOALIAS_TRANSLIT;
                if ($this->fields['autoalias_type'] == 2) $type = '(('.$type.')|'.Catalog::AUTOALIAS_TRANSLIT.')&'.~Catalog::AUTOALIAS_ID;
                if ($this->fields['autoalias_type'] == 1) $type = '(('.$type.')&'.~Catalog::AUTOALIAS_TRANSLIT.')&'.~Catalog::AUTOALIAS_ID;
            }
            
            if (isset($this->fields['cat_inherit'])) {
                if ($this->fields['cat_inherit'])
                    $type = '('.$type.')|'.Catalog::INHERIT;
                    else $type = '('.$type.')&'.~Catalog::INHERIT;
            }	 
            
            if (isset($this->fields['typ']) && $this->materialsType != $this->fields['typ']) {
        		$r = fssql_query("SELECT alias from types where id=".$this->fields['typ']);
        		if (mysql_num_rows($r)) {
        		    $newtable = mysql_result($r,0);
        		    $r = fssql_query("select B.alias,A.name from types_fields A, types B where B.id=A.id and A.type=".FIELD_LINKSET." and A.pseudo_type<>".PSEUDO_FIELD_CATOLOGS." and A.len=".$this->id);
        		    while ($f = mysql_fetch_row($r))
        		        fssql_query("alter table ".$f[0]."_".$this->materialsTable."_".$f[1]." rename ".$f[0]."_".$newtable."_".$f[1]);   
        		}
            }
        }

        fssql_query("UPDATE dir_data SET type=$type $set where id=".$this->id);

        $this->updateCache();

    }
    
    /**
     * @internal	    
     */ 	
    protected function _getMaterials($args)
    {
        
        $fields= isset($args[0])?$args[0]:'*';
        $where = isset($args[1])?$args[1]:'';
        $order = isset($args[2])?$args[2]:'dat DESC';
        $group = isset($args[3])?$args[3]:'';
        $limit = isset($args[4])?$args[4]:'';
        $subs  = isset($args[5])?$args[5]:false;
        $link_fields = isset($args[6])?$args[6]:false;
    
       $res = new Iterator\Object();
       if ($fields != '*') $fields = 'id,idcat,alias,'.$fields;
       $r = $this->select($fields, $where,$order,$group,$limit,$subs, $link_fields);
	     while ($f = mysql_fetch_assoc($r))
            $res->append(Material::fetch($f, $this->materialsType, $this->materialsTable));
       return $res;
    }  
    
    /**
     * Возвращает количество опубликованных материалов в разделе
     *    
	 * @deprecated используйте getMaterials()->getCountAll()
     * @param string $where параметр WHERE формируемого запроса     
     * @param array $link_fields фильтр по полям типа "группа материалов" или "ссылка на группу материалов"
     * @param bool $subs искать материалы в подразделах   
     * @return int
     */
    public function getMaterialsCount($where = '', $link_fields = false, $subs = false)
    {
        if (!$this->materialsType) return 0;
        try {
            $r = $this->select('COUNT(*)', $where,'','','',$subs,$link_fields);
            return (int)mysql_result($r,0);
        } catch (\Exception $e) {
            return 0;
        }
    }  
    
    /**
     * Формирует и выполняет запрос к БД на получение материалов раздела     
     *     
	 * @deprecated используйте getMaterials()
     * @param string $fields список простых полей материала
     * @param string $where параметр WHERE формируемого запроса     
     * @param string $order параметр ORDER BY формируемого запроса    
     * @param string $group параметр GROUP BY формируемого запроса    
     * @param string $limit параметр LIMIT формируемого запроса 
     * @param bool $subs искать материалы в подразделах       
     * @return resource результат SQL запроса 
     */    
    public function select($fields='*',$where='',$order='',$group='',$limit='',$subs = false, $link_field = false) {
                          
        if ($subs) {
            $wherecat = ' and (idcat IN ('.implode(',',$this->getSubs()).'))';
        } else {
            $wherecat = ' and idcat='.$this->prototype->id;
        }
        
        
        $app = Application::getInstance();
        if ($app->previewMode())
            $pub = '1=1';
            else $pub = PUBLISHED;
          
        $sql = 'SELECT '.$fields.' FROM '.$this->materialsTable;
        
        if ($link_field) {

            preg_match_all("|`(\w+)`|U", $link_field, $matches);
            if ($matches[1]) {

                $fields = array_unique($matches[1]);
                
                $flds = $this->materialsObjectDefinition->getFields();
                foreach ($fields as $f) {
                    if ($flds[$f]['type'] != FIELD_LINKSET && $flds[$f]['type'] != FIELD_MATSET) continue;
                    $tableto = ObjectDefinition::get_table($flds[$f]['type'], $flds[$f]['len'], $this->materialsType, $flds[$f]['pseudo_type']);     
                    $sql .= ' LEFT JOIN '.$this->materialsTable.'_'.$tableto.'_'.$f.' '.$f.' USING (id)';
                    $link_field = str_replace("`$f`", "`$f`.dest", $link_field);            
                }
            } else {
            
                $link_field = '';
            
            }

        }
        
        $sql .= ' WHERE '.$pub.$wherecat;
        if ($where) $sql .= ' and ('.$where.')';
        if ($link_field) $sql .= ' and ('.$link_field.')';
        if ($group) $sql .= ' GROUP BY '.$group;
        if ($order) $sql .= ' ORDER BY '.$order;
        if ($limit) $sql .= ' LIMIT '.$limit;

        $r = fssql_query($sql);
        // print $sql;
        return $r;
    }    
	
	public function boArray()
	{
		$a = Application::getInstance();
		$translator = $a->getTranslator();
		$lang_res_perm = array(
			PERM_CAT_OWN_MAT => $translator->_('Работа со своими материалами'),
			PERM_CAT_ALL_MAT => $translator->_('Работа с материалами других авторов'),
			PERM_CAT_ADMIN   => $translator->_('Администрирование раздела'),
			PERM_CAT_VIEW	 => $translator->_('Возможность видеть раздел в структуре'),
			PERM_CAT_MAT_PUB => $translator->_('Публикация материалов')
		);
		
		$permissions = array();
		// матрица разрешений для раздела
		$permissions = array();
		$cat = $this;
		while ($cat->isInheritsPermissions()) $cat = $cat->parent;        	
		
		foreach($lang_res_perm as $pid => $value)
		{
			$gr = array();
			$r = fssql_query('SELECT group_id FROM users_groups_allow_cat WHERE permission='.$pid.' and catalog_id='.$cat->id);
			while ($perm = mysql_fetch_assoc($r)) $gr[] = (int)$perm['group_id'];
			$permissions[$pid] = array(
				'name' => $value,
				'groups' => $gr
			);
		}		
		
		$parent = false;
		if (!$this->isRoot()) $parent = array(
			'id'       => $this->parent->id,
			'treePath' => $this->parent->getTreePath(),
			'path'     => $this->parent->getPath()->implode(),
		);	

		if (!$this->isLink() && !$this->isRoot())
		{				
			$objectRenderer = new ObjectRenderer(new ObjectDefinition(Catalog::TYPE), false, $this->id, -1, $translator->_('Свойства'));   
			$objectRenderer->setObject($this);  
			$userFields	= array(
				'tabs' => $objectRenderer->renderFields(false,true),
				'init' => $objectRenderer->initalizeFields(true),
				'save' => $objectRenderer->saveFields(true),
			);
		} 
		else
		{
			$userFields = null;
		}		
		
		return array(
			'id'       => $this->id,
			'name'     => $this->name,
			'alias'    => $this->alias,
			'is_link'  => $this->isLink(),
			'is_server'=> $this->isServer(),
			'is_root'  => $this->isRoot(),
			'aliases'  => $this->isServer()?$this->getAliases():null,
			'template' => $this->template,
			'templateDir'=> $this->templateDir,
			'materialsCount'=> $this->materialsCount,
			'materialsType' => (int)$this->materialsType,
			'autoalias'     => ($this->catalogType&Catalog::AUTOALIAS)?1:0,
			'autoaliasTranslit' => (int)($this->catalogType&Catalog::AUTOALIAS_TRANSLIT)?1:0,
			'autoaliasId' => (int)($this->catalogType&Catalog::AUTOALIAS_ID)?1:0,
			'hidden'    => (int)$this->hidden,	
			'inheritPermissions' => ($this->isInheritsPermissions()?Catalog::INHERIT:0),
			'inheritFields' => (boolean)$this->inheritFields,
			'prototype' => array(
				'id'       => $this->prototype->id,
				'treePath' => $this->prototype->getTreePath(),
				'path'     => $this->prototype->getPath()->implode(),
			),			
			'parent'   => $parent,
			'permissions' => $permissions,
			'user_fields' => $userFields,
		);
	}
}