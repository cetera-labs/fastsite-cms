<?php
/**
 * Cetera CMS 3 
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
namespace Cetera; 
 
/**
 * Класс для работы со структурой БД
 *  
 * @package CeteraCMS
 */ 
class Schema {
    
    /** Таблица не найдена */
    const TABLE_NOT_EXISTS      = 1;
    /** Неверный тип таблицы */
    const TABLE_WRONG_ENGINE    = 2;
    /** Поле не найдено */
    const FIELD_NOT_FOUND       = 3;
    /** Лишнее поле */
    const EXTRA_FIELD           = 4;
    /** Поля различаются */
    const FIELD_DONT_MATCH      = 5;
    /** Лишний индекс */
    const EXTRA_KEY             = 6;
    /** Индекс не найден */
    const KEY_NOT_FOUND         = 7;
    /** Индексы различаются */
    const KEY_DONT_MATCH        = 8;
    /** Таблица не найдена */
    const TYPE_NOT_EXISTS       = 100;   
    /** Поле не найдено */
    const TYPE_FIELD_NOT_FOUND  = 101;
    /** Поля различаются */
    const TYPE_FIELD_DONT_MATCH = 102;  
    /** Виджет не найден */
    const WIDGET_NOT_EXISTS     = 200;     
    
    /**
     * Список файлов со структурой таблиц и данными для модулей системы    
     * @var array
     */         
    public $schemas = false;
    
    private $dbConnection = false;
    
    /**
     * Установлено ли соединение с БД
     *      
     * @return void
     */
    public function __construct()
    {
    
    	$this->schemas = array(
          'core' => array(
              'schema' => DB_SCHEMA,
              'sql'    => DB_DATA
          )
      );
      
      $this->dbConnection = Application::getInstance()->getConn();
    			
    	foreach (Plugin::enum() as $id => $plugin) { 
        $id = 'plugin_'.$id;
    		$this->schemas[$id] = array(
    			'name'   => 'Plugin <b>'.$plugin->name.'</b>'
    		);
    		if (isset($plugin['schema'])) $this->schemas[$id]['schema'] = $plugin['schema'];
    		if (isset($plugin['sql']))    $this->schemas[$id]['sql']    = $plugin['sql'];
    	}  
      
    	foreach (Theme::enum() as $id => $theme) { 
        $id = 'theme_'.$id;
    		$this->schemas[$id] = array(
    			'name'   => 'Theme <b>'.$theme->name.'</b>'
    		);
    		if (isset($theme['schema'])) $this->schemas[$id]['schema'] = $theme['schema'];
    		if (isset($theme['sql']))    $this->schemas[$id]['sql']    = $theme['sql'];
    	} 
      
    }
    
    /*
     * Проверяет существует ли таблица в БД, соответствует ли она схеме БД.
     * В случае расхождений, выполняется корректирующий SQL запрос.
     * 
     * @param string $table имя таблицы
     * @param string $module имя модуля         
     * @return bool          
     */
    public function get_fix_query($tables, $error)
    {    
    		$query = '';
    		switch($error['error']){
    			case self::TABLE_NOT_EXISTS: 
    				$query = $this->getCreateTable($tables[$error['table']]);
    				break;
    			case self::TABLE_WRONG_ENGINE:
    				$query = 'ALTER TABLE `'.$error['table'].'` ENGINE='.$tables[$error['table']]['engine'];
    				break;
    			case self::FIELD_NOT_FOUND:
					$field = $tables[$error['table']]['fields'][$error['field']];
    				$query = 'ALTER TABLE `'.$error['table'].'` ADD `'.$error['field'].'` '.$this->getFieldDef($field, false);
					if (isset($field['auto_increment']) && $field['auto_increment']) {
						$query .= ";\nALTER TABLE `".$error['table'].'` ADD INDEX tmp_'.$error['field'].' (`'.$error['field'].'`)';
						$query .= ";\nALTER TABLE `".$error['table'].'` CHANGE `'.$error['field'].'` `'.$error['field'].'` '.$this->getFieldDef($field);
						//$query .= ";\nALTER TABLE `".$error['table'].'` CHANGE `'.$error['field'].'` `'.$error['field'].'` '.$this->getFieldDef($field, false);
						//$query .= ";\nALTER TABLE `".$error['table'].'` ADD PRIMARY KEY (`'.$error['field'].'`)';
						//$query .= ";\nALTER TABLE `".$error['table'].'` DROP INDEX tmp_'.$error['field'];
					}
    				break;
    			case self::EXTRA_FIELD:
    				$query = 'ALTER TABLE `'.$error['table'].'` DROP `'.$error['field'].'`';
    				break;
    			case self::FIELD_DONT_MATCH:
    				$query = 'ALTER TABLE `'.$error['table'].'` CHANGE `'.$error['field'].'` `'.$error['field'].'` '.$this->getFieldDef($tables[$error['table']]['fields'][$error['field']]);
    				break;		
    			case self::EXTRA_KEY:
    				$query = 'ALTER TABLE `'.$error['table'].'` DROP ';
    				if ($error['found'] == 'PRIMARY') 
    					$query .= 'PRIMARY KEY';
    					else $query .= 'INDEX '.$error['found'];
    				break;
    			case self::KEY_NOT_FOUND:
    				$query = 'ALTER TABLE `'.$error['table'].'` ADD '.$this->getIndexDef($tables[$error['table']]['keys'][$error['kid']]);
    				break;
    			case self::KEY_DONT_MATCH:
    				$query = 'ALTER TABLE `'.$error['table'].'` DROP ';
    				if ($tables[$error['table']]['keys'][$error['kid']]['name'] == 'PRIMARY')
    					$query .= 'PRIMARY KEY';
    					else $query .= 'INDEX '.$tables[$error['table']]['keys'][$error['kid']]['name']; 
    				$query .= ', ADD '.$this->getIndexDef($tables[$error['table']]['keys'][$error['kid']]);
    				break;
    		} // switch
    		return $query;
    }
    
    /*
     * Проверяет соответствие структуры таблиц реальным таблицам БД.
     * 
     * @param bool $ignore_extra_fields игнорировать наличие лишних полей в таблице     
     * @param bool $ignore_extra_keys игнорировать наличие лишних индексов в таблице          
     * @return array результат проверки
     */
    public function compare_schemas($ignore_extra_fields = TRUE, $ignore_extra_keys = FALSE)
    {
       
    	$res = array();
    	foreach ($this->schemas as $id => $scheme) {
    		$a = $this->parseSchema($id);
        
    		if ($a['tables']) foreach ($a['tables'] as $table) {
				$r = $this->compareTable($id, $table, $ignore_extra_fields, $ignore_extra_keys);
				$res = array_merge($res, $r);
    		}
        
    		if ($a['types']) foreach ($a['types'] as $type) {
				$r = $this->compareType($id, $type);
				$res = array_merge($res, $r);
    		}                          
        
    		if ($a['widgets']) foreach ($a['widgets'] as $widget) {
				$r = $this->compareWidget($id, $widget);
				$res = array_merge($res, $r);
    		}        
        
    	}
    	return $res;
    }
    
    /*
     * Создает в БД таблицы модуля.
     * 
     * @param string $module имя модуля  
     * @param boolean $drop удалять предыдущие версии таблиц                 
     * @return void
     */
    public function readSchema($module, $drop = true)
    {			
		$res = $this->parseSchema($module);
    	if (is_array($res['tables'])) 
			foreach ($res['tables'] as $table) 
			{
				if ($drop) $this->dbConnection->executeQuery('DROP TABLE IF EXISTS `'.$table['name'].'`');
				$this->dbConnection->executeQuery($this->getCreateTable($table));
			}
        
        $this->fixTypes($res['types']);
		$this->fixWidgets($res['widgets']);
     
    }
    
    public function fixSchema($module)
    {			
        $res = array();
    	$a = $this->parseSchema($module);
    	if ($a['tables']) foreach ($a['tables'] as $table) 
		{
            $r = $this->compareTable($module, $table, true, true);
            $res = array_merge($res, $r);
    	}
        if (sizeof($res)) foreach ($res as $error) {
            $this->dbConnection->executeQuery( $this->get_fix_query($a['tables'], $error) );
        }
        
        $this->fixTypes($a['types']);
        
        $this->fixWidgets($a['widgets']);
    } 
    
    /*
     * Удаляет из БД таблицы модуля.
     * 
     * @param string $module имя модуля                
     * @return void
     */
    public function dropSchema($module)
    {			
    		$res = $this->parseSchema($module);
    		if (is_array($res['tables'])) foreach ($res['tables'] as $table) {
            $this->dbConnection->executeQuery('DROP TABLE IF EXISTS `'.$table['name'].'`');
    		}
    		if (is_array($res['types'])) foreach ($res['types'] as $type) {
            try {
                ObjectDefinition::findByTable($type['name'])->delete();
            } catch (\Exception $e) {}
    		}        
    }  

    public function fixWidgets($widgets)
    {       
        $a = Application::getInstance();
		
    	if (is_array($widgets)) foreach ($widgets as $widget_info)
		{   
	
			try
			{
				$w = Application::getInstance()->getWidget( $widget_info['widgetAlias'] );
			} 
			catch (\Exception $e)
			{
				
				$widget = $a->getWidget( 'Container' );
				$widget->widgetAlias = $widget_info['widgetAlias'];
				$widget->widgetTitle = $widget_info['widgetTitle'];
				$widget->widgetDisabled = $widget_info['widgetDisabled'];
				$widget->widgetProtected = $widget_info['widgetProtected'];
				
				foreach ($widget_info['widgets'] as $child_info)
				{				
					$child = $a->getWidget( $child_info['widgetName'] );
					$child->widgetTitle = $child_info['widgetTitle'];
					$child->setParams( unserialize( $child_info['params'] ) );
					$child->save();
					$widget->addWidget( $child->getId() );				
				}
				
				$widget->save();				

			}	            
            
    	}      
    } 
    
    public function fixTypes($types)
    {       
    
    	if (is_array($types)) foreach ($types as $type) 
		{		
            try 
			{
                $od = ObjectDefinition::findByTable($type['name'])->update($type);
            } catch (\Exception $e) {
                $od = ObjectDefinition::create($type);
            }            
            foreach($type['fields'] as $field) {
            
                try {
                    $f = $od->getField($field['name']);
                } catch (\Exception $e) {                
                    $od->addField($field);
                    continue;
                }
                $field['field_id'] = $f['field_id'];
                $od->updateField($field);
                
            }
    	}      
    }       
	
    /*
     * Импортирует в БД данные модуля.
     * 
     * @param string $module имя модуля             
     * @return viod
     */
    public function readDump($module)
    {		
		$sql_file = $this->schemas[$module]['sql'];
		return $this->readDumpFile($sql_file);
    }  
     
    public function readDumpFile($sql_file, $fast = false) 
    {
    	if (!$sql_file) return FALSE;
	    if (!file_exists($sql_file) || !is_file($sql_file)) throw new Exception\CMS(Exception\CMS::FILE_NOT_FOUND, $sql_file);
		
		if (!filesize($sql_file)) return;
					
		if ($fast) {
			$sql_query = fread(fopen($sql_file, 'r'), filesize($sql_file));
			if (get_magic_quotes_runtime() == 1) $sql_query = stripslashes($sql_query);
			$sql_query    = trim($sql_query);
			$this->dbConnection->executeQuery($sql_query);
		}
		else {
			
			$templine = '';
			$lines = file($sql_file); // Read entire file

			foreach ($lines as $line){
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*' )
					continue;

				// Add this line to the current segment
				$templine .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';')
				{
					$this->dbConnection->executeQuery($templine);
					$templine = '';
				}
		    }
		}
			
    	return;
    }

    /*
     * Удаляет комментарии из SQL запроса.
     * 
     * @param string $sql запрос             
     * @return string
     */
    private function removeRemarks($sql)
    {
        $i = 0;
    
        while ($i < mb_strlen($sql)) {
            if (mb_substr($sql,$i,1) == '#' && ($i == 0 || mb_substr($sql,$i-1,1) == "\n")) {
                $j = 1;
                while (mb_substr($sql,$i+$j,1) != "\n") {
                    $j++;
                    if ($j+$i > mb_strlen($sql)) {
                        break;
                    }
                } // end while
                $sql = mb_substr($sql, 0, $i) . mb_substr($sql, $i+$j);
            } // end if
            $i++;
        } // end while
    
        return $sql;
    } 

    /*
     * Разбивает SQL на отдельные запросы
     * 
     * @param string $sql запрос             
     * @return array
     */
    private function splitSqlFile($sql, $delimiter)
    {
        $sql               = trim($sql);
        $char              = '';
        $last_char         = '';
        $ret               = array();
        $string_start      = '';
        $in_string         = FALSE;
        $escaped_backslash = FALSE;
    
        for ($i = 0; $i < mb_strlen($sql); ++$i) {
            $char =  mb_substr($sql, $i, 1); //$sql[$i];
    
            // if delimiter found, add the parsed part to the returned array
            if ($char == $delimiter && !$in_string) {
                $ret[]     = mb_substr($sql, 0, $i);
                $sql       = mb_substr($sql, $i + 1);
                $i         = 0;
                $last_char = '';
            }
    
            if ($in_string) {
                // We are in a string, first check for escaped backslashes
                if ($char == '\\') {
                    if ($last_char != '\\') {
                        $escaped_backslash = FALSE;
                    } else {
                        $escaped_backslash = !$escaped_backslash;
                    }
                }
                // then check for not escaped end of strings except for
                // backquotes than cannot be escaped
                if (($char == $string_start)
                    && ($char == '`' || !(($last_char == '\\') && !$escaped_backslash))) {
                    $in_string    = FALSE;
                    $string_start = '';
                }
            } else {
                // we are not in a string, check for start of strings
                if (($char == '"') || ($char == '\'') || ($char == '`')) {
                    $in_string    = TRUE;
                    $string_start = $char;
                }
            }
            $last_char = $char;
        } // end for
    
        // add any rest to the returned array
        if (!empty($sql)) {
            $ret[] = $sql;
        }
        return $ret;
    }

    /*
     * Проверяет существует ли Виджет.
     * 
     * @param string $module имя модуля         
     * @param string $widget alias виджета        
     * @return array результат проверки
     */
    private function compareWidget($module, $widget)
    {
        $res = array();
        
        try {
            $w = Application::getInstance()->getWidget( $widget['widgetAlias'] );
        } catch (\Exception $e) {
      		  $res[] = array(
      				'error'  => self::WIDGET_NOT_EXISTS,
      				'widget' => $widget['widgetAlias'],
      				'module' => $module
      			);
        }
        
        return $res;  
    }
    
    /*
     * Проверяет существует ли тип материалов в БД, соответствует ли он схеме.
     * 
     * @param string $module имя модуля         
     * @param string $type alias типа материалов        
     * @return array результат проверки
     */
    private function compareType($module, $type)
    {
        $res = array();
        
        try {
            $od = ObjectDefinition::findByTable($type['name']);
        } 
		catch (\Exception $e) {
      		  $res[] = array(
      				'error'  => self::TYPE_NOT_EXISTS,
      				'table'  => $type['name'],
      				'module' => $module
      		  );
      		  return $res;        
        }
        
        if (is_array($type['fields'])) foreach ($type['fields'] as $field) {
            try {
                $f = $od->getField($field['name']);
                
          		foreach ($field as $prop => $value) 
				{
						if ($prop == 'description') continue;
						if ($prop == 'editor_user') continue;
						if (!$f['fixed']) {
						    if ($prop == 'editor') continue;
						}
						
						if ( $prop == 'length' && !(int)$value )
						{
							$_od = ObjectDefinition::findById($f[$prop]);
							$f[$prop] = $_od->alias;
						}
						
						if ($value != $f[$prop]) {
									$res[] = array(
										'error'    => self::TYPE_FIELD_DONT_MATCH,
										'field'    => $field['name'],
										'table'    => $type['name'],
										'module'   => $module,
										'expected' => $prop.' => '.$value,
										'found'    => $prop.' => '.$f[$prop],
									);
						}
                }                
            } 
			catch (\Exception $e)
			{
        			  $res[] = array(
          					'error'  => self::TYPE_FIELD_NOT_FOUND,
          					'field'  => $field['name'],
          					'table'  => $type['name'],
          					'module' => $module
        				);      
            }
                    
        }
        
        return $res;
    
    }    

    
    /*
     * Проверяет существует ли таблица в БД, соответствует ли она схеме БД.
     * 
     * @param string $module имя модуля         
     * @param string $table информация о таблице  
     * @param bool $ignore_extra_fields игнорировать наличие лишних полей в таблице     
     * @param bool $ignore_extra_keys игнорировать наличие лишних индексов в таблице          
     * @return array результат проверки
     */
    private function compareTable($module, $table, $ignore_extra_fields = TRUE, $ignore_extra_keys = FALSE)
    {
        $res = array();
    
		$dbtable = $this->describeTable($table['name']);
		
		if (!$dbtable) {
		    $res[] = array(
				'error'  => self::TABLE_NOT_EXISTS,
				'table'  => $table['name'],
				'module' => $module
			);
			return $res;
		}
		
		if ($table['engine'] && $dbtable['engine'] != $table['engine']) {
		    $res[] = array(
				'error'    => self::TABLE_WRONG_ENGINE,
				'table'    => $table['name'],
				'module'   => $module,
				'expected' => $table['engine'],
				'found'    => $dbtable['engine']
			);    
		}
		
		if (is_array($table['fields'])) foreach ($table['fields'] as $fname => $field) {
			if (!isset($dbtable['fields'][$fname])) {
			    $res[] = array(
					'error'  => self::FIELD_NOT_FOUND,
					'field'  => $fname,
					'table'  => $table['name'],
					'module' => $module
				);
				continue;		    
			}
			
			$def   = $this->getFieldDef($field);
			$dbdef = $this->getFieldDef($dbtable['fields'][$fname]);
			if ($def != $dbdef) 
				$res[] = array(
					'error'  => self::FIELD_DONT_MATCH,
					'field'  => $fname,
					'table'  => $table['name'],
					'module' => $module,
					'expected' => $def,
					'found'    => $dbdef
				);
			unset($dbtable['fields'][$fname]);
		}
		if (!$ignore_extra_fields && $table['name'] != 'dir_data') 
			if (is_array($dbtable['fields'])) 
				foreach ($dbtable['fields'] as $fname => $field) 
				    $res[] = array(
						'error'  => self::EXTRA_FIELD,
						'field'  => $fname,
						'table'  => $table['name'],
						'module' => $module
					);		
		
				
		if (is_array($table['keys'])) foreach ($table['keys'] as $kid => $key) {
			if (!isset($dbtable['keys'][$kid])) {
			    $res[] = array(
					'error'    => self::KEY_NOT_FOUND,
					'field'    => $this->getIndexDef($key),//$key['name'].' ('.implode(',', $key['columns']).')',
					'table'    => $table['name'],
					'module'   => $module,
					'kid'	   => $kid,
				);
				continue;		    
			}
						
			if (($dbtable['keys'][$kid]['columns'] != $key['columns'])||($dbtable['keys'][$kid]['unique'] != $key['unique'])) {
			    $res[] = array(
					'error'  => self::KEY_DONT_MATCH,
					'field'  => $key['name'],
					'table'  => $table['name'],
					'module' => $module,
					'kid'	 => $kid,
					'expected' => $this->getIndexDef($key),//'('.implode(',', $key['columns']).')'.($key['unique']?' UNIQUE':''),
					'found'    => $this->getIndexDef($dbtable['keys'][$kid]),//'('.implode(',', $dbtable['keys'][$kid]['columns']).')'.($dbtable['keys'][$kid]['unique']?' UNIQUE':'')
				);
			}
			
			unset($dbtable['keys'][$kid]);
		}
		
		if (!$ignore_extra_keys) 
			if (is_array($dbtable['keys'])) 
				foreach ($dbtable['keys'] as $kid => $key) 
				    $res[] = array(
						'error'  => self::EXTRA_KEY,
						'field'  => $key['name'].' ('.implode(',', $key['columns']).')',
						'table'  => $table['name'],
						'module' => $module,
						'found'	 => $key['name'],
						'kid'	 => $kid
					);	
    						
    	return $res;
    }
    
    /*
     * Формирует SQL запрос на создание таблицы
     * 
     * @param array $table информация о таблице
     * @param string $charset default charset таблицы              
     * @return string          
     */
    private function getCreateTable($data, $charset = 'utf8')
    {
    	$sql  = "CREATE TABLE `".$data['name']."` (\n";
    	
    	if (is_array($data['fields'])) foreach ($data['fields'] as $field) {
    	    $sql .= "    `".$field['name']."` ".$this->getFieldDef($field).",\n";
    	}
    	
    	if (is_array($data['keys'])) foreach ($data['keys'] as $key) 
    		$sql .= '    '.$this->getIndexDef($key).",\n";
    	
    	$sql = substr($sql, 0, -2)."\n)";
    	if (isset($data['engine'])) $sql .= ' ENGINE='.$data['engine'];
    	if ($charset) $sql .= ' DEFAULT CHARSET '.$charset;
    	$sql .= ";\n";
    	return $sql;
    }  
    
    /*
     * Формирует часть SQL запроса с описанием поля таблицы
     * 
     * @param array $field информация о поле           
     * @return string          
     */
    private function getFieldDef($field, $auto_increment = true)
    {
    	$sql = $field['type'];
    	if ($field['null'] == 0) $sql .= ' NOT NULL'; else $sql .= ' NULL';
    	if (isset($field['default'])) 
           $sql .= " default '".$field['default']."'";
    	   elseif ((!isset($field['auto_increment']) || !$field['auto_increment'])&&(substr_count(strtolower($field['type']),'int')>0 || substr_count(strtolower($field['type']),'double'))>0)
    	      $sql .= " default '0'"; 
    	if ($auto_increment && isset($field['auto_increment']) && $field['auto_increment']) $sql .= " auto_increment";
    	return $sql;
    }
    
    /*
     * Формирует часть SQL запроса с описанием индекса таблицы
     * 
     * @param array $key информация об индексе           
     * @return string          
     */
    private function getIndexDef($key)
    {
    	$sql = '';
    	if ($key['name'] == 'PRIMARY') 
    		$sql .= "PRIMARY KEY (";
    		else {
    			if ($key['unique']) $sql .= "UNIQUE ";
    			$sql .= "INDEX `".$key['name']."` (";
    		}
		
		foreach ($key['columns'] as $i => $col) {
			if ($i) $sql .= ',';
			$sql .= '`'.$col['name'].'`';
			if ($col['length']) {
				$sql .= '('.$col['length'].')';
			}
		}
		
    	$sql .= ")";
    	return $sql;
    }
    
    /*
     * Считывает из БД информацию о таблице
     * 
     * @param string $tname имя таблицы           
     * @return array          
     */
    private function describeTable($tname) {
    	$tdescr = $this->dbConnection->fetchAssoc('SHOW TABLE STATUS LIKE "'.$tname.'"');
    	if (!$tdescr) return FALSE;
    	if ($tdescr['Engine'] == 'HEAP') $tdescr['Engine'] = 'MEMORY';
    	
    	$table = array('name' => $tname, 'engine' => $tdescr['Engine']);
    	$r = $this->dbConnection->executeQuery('DESCRIBE `'.$tname.'`');
    	while($f = $r->fetch())
      {
    		$nulls = ($f['Null'] && $f['Null'] != 'NO')?1:0;
    		$field = array(
    			'name' => $f['Field'],
    			'type' => $f['Type'],
    			'null' => $nulls
    		);
    		if (isset($f['Default']) && strlen($f['Default'])) $field['default'] = $f['Default'];
    		if ($f['Extra'] == 'auto_increment')  $field['auto_increment'] = 1;
    		if (!isset($table['fields'])) $table['fields'] = array();
    		 $table['fields'][$field['name']] = $field;
    	} // while
    	
    	$r = $this->dbConnection->executeQuery('SHOW KEYS FROM `'.$tname.'`');
    	$prevkey = FALSE;
    	while($f = $r->fetch())
      {
    		if ($prevkey != $f['Key_name']) {
    			if ($prevkey) {
    				if (!isset($table['keys'])) $table['keys'] = array();
    				$table['keys'][$key['name']] = $key;
    			}
    			$key = array(
    				'name'    => $f['Key_name'],
    				'unique'  => 1-$f['Non_unique'],
    				'columns' => array()
    			);	    
    		}
    		$col = array(
				'name' => $f['Column_name']
			);
			if ($f['Sub_part']) {
				$col['length'] = $f['Sub_part'];
			}
			$key['columns'][] = $col;
			
    		$prevkey = $f['Key_name'];
    	}
    	if ($prevkey) {
    		if (!isset($table['keys'])) $table['keys'] = array();
    		$table['keys'][$key['name']] = $key;
    	}
		    	
    	return $table;
    }
    
    /*
     * Разбирает XML файл со структурой таблиц
     * 
     * @param string $module имя модуля         
     * @return array список таблиц         
     */
    public function parseSchema($module) {
        
      if (!isset($this->schemas[$module])) return false;
      if (!isset($this->schemas[$module]['schema'])) return false;
      
      $file = $this->schemas[$module]['schema'];
      
    	if (!file_exists($file) || !is_file($file)) throw new Exception\CMS(Exception\CMS::FILE_NOT_FOUND, $file);
    
    	$data = implode("",file($file));
    	$parser = xml_parser_create();
    	xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
    	xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
    	xml_parse_into_struct($parser,$data,$values,$tags);
    	xml_parser_free($parser);
    	
    	$res = array(
          'tables' => array(),
          'types'  => array(),
          'widgets'=> array()
      );
      
    	foreach ($values as $value) {
      
    		if ($value['tag'] == 'table') {
    		    if ($value['type'] == 'open') $table = $value['attributes'];
    		    if ($value['type'] == 'close') {
                    if (isset($table['engine']) && $table['engine'] == 'HEAP') $table['engine'] = 'MEMORY';
                    $res['tables'][$table['name']] = $table;
                }
    		}
        
    		if ($value['tag'] == 'widgetcontainer') {
    		    if ($value['type'] == 'open') {
                $widget = $value['attributes'];
                $widget['widgets'] = array();
            }
    		    if ($value['type'] == 'close') $res['widgets'][$widget['widgetAlias']] = $widget;
    		}
        
        if ($value['tag'] == 'widget') {
            $value['attributes']['params'] = trim($value['value']);
            $widget['widgets'][] = $value['attributes'];
        }  
        
    		if ($value['tag'] == 'object') {
    		    if ($value['type'] == 'open') $table = $value['attributes'];
    		    if ($value['type'] == 'close') $res['types'][$table['name']] = $table;
    		}        
        
    		if ($value['tag'] == 'field' && isset($value['attributes']['name'])) {
    			if (!isset($table['fields'])) $table['fields'] = array();
    			$table['fields'][$value['attributes']['name']] = $value['attributes'];
    		}
        
    		if ($value['tag'] == 'key') {
    		    if ($value['type'] == 'open') $key = $value['attributes'];
    			if ($value['type'] == 'close') {
    				if (!isset($table['keys'])) $table['keys'] = array();	
    				$table['keys'][$key['name']] = $key;
    			}
    		}
        
    		if ($value['tag'] == 'column' && isset($value['attributes']['name'])) {
    			if (!isset($key['columns'])) $key['columns'] = array();
    			$key['columns'][] = $value['attributes'];
    		}
        
    	}
		
		if ($module == 'core') {
			try {
				foreach (ObjectDefinition::enum() as $od) {
					foreach ($od->getFields() as $f) {
						if (is_subclass_of($f, '\\Cetera\\ObjectFieldLinkSetAbstract')) {
							
							$res['tables'][$f->getLinkTable()] = [
								'name'   => $f->getLinkTable(),
								'fields' => [
									'id' => [
										'name' => 'id',
										'type' => 'int(11)',
										'null' => 0
									],
									'dest' => [
										'name' => 'dest',
										'type' => 'int(11)',
										'null' => 0
									],
									'tag' => [
										'name' => 'tag',
										'type' => 'int(11)',
										'default' => 0,
										'null' => 0
									],
								],
								'keys' => [
									'PRIMARY' => [
										'name' => 'PRIMARY',
										'unique' => 1,
										'columns' => [
											['name' => 'id'],
											['name' => 'dest'],
										]
									],
									'dest' => [
										'name' => 'dest',
										'unique' => 0,
										'columns' => [
											['name' => 'dest'],
										]
									],
								]
							];
							
						}
					}
				}
			}
			catch (\Exception $e) {}
			
		}		
      
    	return $res;
    }
    
    public function createXml($tables = false, $types = false) {
    
        $xml  = '<'.'?xml version="1.0"?'.'>'."\n";
        $xml .= '<schema>'."\n\n";
        
        if (is_array($tables)) foreach ($tables as $t) $xml .= $this->xmlTable($t)."\n";
        if (is_array($types)) foreach ($types as $t) $xml .= $this->objectDefinition($t)."\n";
        
        $xml .= '</schema>'."\n";
        return $xml;
    }

    /*
     * Формирует XML с информацией о таблице
     * 
     * @param string $tname имя таблицы           
     * @return string         
     */    
    public function xmlTable($tname) {
    	$table = $this->describeTable($tname);
    	if (!$table) return '';
    
    	$xml = '<table name="'.$table['name'].'" engine="'.$table['engine'].'">'."\n";
    	
    	if (is_array($table['fields'])) foreach ($table['fields'] as $field) {
    	    $xml .= '    <field';
    		foreach ($field as $name => $value) $xml .= ' '.$name.'="'.$value.'"';
    		$xml .= " />\n";
    	}
    	
    	if (is_array($table['keys'])) foreach ($table['keys'] as $key) {
    		$xml .= '    <key';
    		foreach ($key as $name => $value) if (!is_array($value)) $xml .= ' '.$name.'="'.$value.'"';
    		$xml .= ">\n";
    		foreach ($key['columns'] as $column) $xml .= '        <column name="'.$column.'" />'."\n";
    		$xml .= "    </key>\n";
    	}
    
    	$xml .= "</table>\n";
    	return $xml; 
    }
    
    public function objectDefinition($alias) {
        $od = new ObjectDefinition($alias);
        $xml = '<object name="'.$od->table.'" description="'.htmlspecialchars($od->description).'" fixed="'.(int)$od->fixed.'" handler="'.htmlspecialchars($od->handler).'" plugin="'.htmlspecialchars($od->plugin).'">'."\n";
      	
        foreach($od->getFields() as $f) {
        
            $xml .= '    <field name="'.htmlspecialchars($f['name']).'" type="'.(int)$f['type'].'" pseudo_type="'.(int)$f['pseudo_type'].'" description="'.htmlspecialchars($f['describ']).'" length="'.(int)$f['len'].'" show="'.(int)$f['shw'].'" required="'.(int)$f['required'].'" fixed="'.(int)$f['fixed'].'" editor="'.(int)$f['editor'].'" editor_user="'.htmlspecialchars($f['editor_user']).'" default_value="'.htmlspecialchars($f['default_value']).'" />'."\n";
        
        }
        
        $xml .= "</object>\n\n";
      	return $xml;         
    }
}
