<?php
namespace Cetera;
/**
 * Cetera CMS 3 
 * 
 * AJAX-backend действия с типами материалов
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

include('common_bo.php');

if (!$user->allowAdmin())  throw new Exception\CMS(Exception\CMS::NO_RIGHTS);

$res = array(
    'success' => false,
    'message' => $translator->_('Неизвестная ошибка')
);

try {

    if ($_REQUEST['action'] == 'field_create' || $_REQUEST['action'] == 'field_edit') {
		
		$type_id       = (int)$_REQUEST['type_id'];
		
		if (isset($_REQUEST['rows']))
		{
			$data = json_decode($_REQUEST['rows'], true);
			
			if (!isset($data['id'])) {
				$data = array_pop($data);
			}
			
			$field_id       = (int)$data['id'];
			$name          = trim($data['name']);
			$describ       = $data['describ'];
			$type          = (int)$data['type'];
			$len           = $data['len'];
			$shw           = (int)$data['shw'];
			$required      = (int)$data['required'];
			$default_value = $data['default_value'];
			$editor        = (int)$data['editor'];
			$editor_user   = $data['editor_user'];
			$page          = $data['page'];			
		}
		else
		{  
			$field_id       = (int)$_REQUEST['field_id'];
			$name          = trim($_REQUEST['name']);
			$describ       = $_REQUEST['describ'];
			$type          = (int)$_REQUEST['type'];
			$len           = (int)$_REQUEST['len'];
			$shw           = 1-(int)$_REQUEST['hidden'];
			$required      = (int)$_REQUEST['required'];
			$default_value = $_REQUEST['default_value'];
			$editor        = (int)$_REQUEST['editor'];
			$editor_user   = $_REQUEST['editor_user'];
			$page          = $_REQUEST['page'];
			
			if ($type==FIELD_MATSET || $type==FIELD_MATERIAL) {
				 $len = $_REQUEST['types'];
				 if (!$len) throw new Exception\Form(Exception\CMS::FIELD_REQUIRED ,'types');
			} 
			elseif ($type==FIELD_LINK || $type==FIELD_LINKSET) {
				if ($_REQUEST['cat']) {
					 $len = $_REQUEST['catid'];
					 if (!$len) throw new Exception\Form(Exception\CMS::CHOOSE_CATALOG ,'catid');
				} else {
					$len = 0;
				}
			}
			elseif ($type==FIELD_ENUM) {
				$len = $_REQUEST['variants'];
			}
			elseif ($type==FIELD_TEXT) {
				 if (!$len) throw new Exception\Form(Exception\CMS::FIELD_REQUIRED ,'len');
			} 
			else {
				$len = 1;
			}			
			
		}
        
        if (!$type_id) throw new Exception\CMS(Exception\CMS::UNKNOWN);
                    
        $pseudo_type = 0;
        
        if ($type > 1000) {
        
        	$pseudo_type = $type;
            $type = $pseudo_to_original[$pseudo_type]['original'];
            
            if (isset( $pseudo_to_original[$pseudo_type]['len'] )) {
                $len = $pseudo_to_original[$pseudo_type]['len'];
            }
            
            switch($pseudo_type){
              	case PSEUDO_FIELD_FILESET: 

            		  $r = fssql_query('SELECT id FROM types WHERE alias="material_files"');
            		  if (!mysql_num_rows($r)) {
							  $od = ObjectDefinition::create(array(
								  'alias'   => 'material_files', 
								  'describ' => $translator->_('Файлы'), 
								  'fixed'   => 1
							  ));
							  $len = $od->id;
							  $od->addField(array(
								  'name' => 'file', 
								  'type' => FIELD_FILE, 
								  'pseudo_type' => 0, 
								  'describ' => $l_field_types[FIELD_FILE], 
								  'len' => 0, 
								  'shw' => 1, 
								  'required' => 1, 
								  'fixed' => 0, 
								  'editor' => 0, 
								  'editor_user' => 0, 
								  'default_value' => ''
							  ));
							  $od->addField(array(
								  'name' => 'text', 
								  'type' => FIELD_LONGTEXT, 
								  'pseudo_type' => 0, 
								  'describ' => $l_field_types[FIELD_TEXT], 
								  'len' => 0, 
								  'shw' => 1, 
								  'required' => 0, 
								  'fixed' => 0, 
								  'editor' => 0, 
								  'editor_user' => EDITOR_DHTML_SHORT, 
								  'default_value' => ''
							  ));
            			  } else {
            				  $len = mysql_result($r,0);
            			  }
                		break;
                    
              	case PSEUDO_FIELD_TAGS: 

              			$r = fssql_query('SELECT id FROM types WHERE alias="material_tags"');
              			if (!mysql_num_rows($r)) {	  
              				$od = ObjectDefinition::create(array(
                          'alias'   => 'material_tags', 
                          'describ' => $translator->_('Ключевые слова'), 
                          'fixed'   => 1
                      ));
                      $len = $od->id;
              			} else {
              				$len = mysql_result($r,0);
              			}
                		break;
                    
                case PSEUDO_FIELD_CATOLOGS: 

                    $len = $_REQUEST['catid_l'];
                		break;
                    
            } // switch
        }
        
        if ($type==FIELD_ENUM && !check_enum($len)) {
        
            throw new Exception\Form(Exception\CMS::ENUM_FIELD ,'variants');
            
        } else {
                   
            $od = new ObjectDefinition($type_id);
          	
          	if ($_REQUEST['action'] == 'field_create') {
            
                      $od->addField(array(
                          'name' => $name, 
                          'type' => $type, 
                          'pseudo_type' => $pseudo_type, 
                          'describ' => $describ, 
                          'len' => $len, 
                          'shw' => $shw, 
                          'required' => $required, 
                          'fixed' => 0, 
                          'editor' => $editor, 
                          'editor_user' => $editor_user, 
                          'default_value' => $default_value,
                          'page' => $page,
                      ));            
                
            } else {
            
                      $od->updateField(array(
                          'field_id' => $field_id,
                          'name' => $name, 
                          'type' => $type, 
                          'pseudo_type' => $pseudo_type, 
                          'describ' => $describ, 
                          'len' => $len, 
                          'shw' => $shw, 
                          'required' => $required, 
                          'fixed' => 0, 
                          'editor' => $editor, 
                          'editor_user' => $editor_user, 
                          'default_value' => $default_value,
                          'page' => $page,
                      ));                      
            
            }
            
            $res['success'] = true; 
        }
    }
    
    if ($_REQUEST['action'] == 'field_delete') {
    
        $r = fssql_query("select type, id, name, len, pseudo_type from types_fields where field_id=".$_REQUEST['id']);
        if (mysql_num_rows($r)) {
            $f = mysql_fetch_assoc($r);
            
            if ($f['type'] > 0) {
            
                $od = new ObjectDefinition($f['id']);             

                // УДАЛЕНИЕ ПОЛЯ
                if (($f['type']!=FIELD_LINKSET)&&($f['type']!=FIELD_MATSET)) {
              	  fssql_query("alter table `".$od->table."` drop `".trim($f['name'])."`");
              	} else {
              	  ObjectDefinition::drop_link_table($od->table, $f['name'], $f['type'], $f['len'], $f['id'], $f['pseudo_type']);
              	}
            }
            
        	  fssql_query("delete from types_fields where field_id=".$_REQUEST['id']);
        	  $res['success'] = true;
    	 }
    }
    
    if ($_REQUEST['action'] == 'field_up') {
      $r = fssql_query("select tag, id from types_fields where field_id=".(int)$_POST['id']);
      list($tag, $type_id) = mysql_fetch_row($r);
      $r = fssql_query("select field_id, tag from types_fields where id=$type_id and tag<$tag order by tag desc limit 1");
      $f = mysql_fetch_row($r);
      if ($f[0]) {
    	  if ($f[1] == $tag) $tag++;
    	  fssql_query("update types_fields set tag=$f[1] where field_id=".(int)$_POST['id']);
    	  fssql_query("update types_fields set tag=$tag where field_id=".(int)$f[0]);
      }
      $res['success'] = true;
    }
    
    if ($_REQUEST['action'] == 'field_down') {
      $r = fssql_query("select tag, id from types_fields where field_id=".(int)$_POST['id']);
      list($tag, $type_id) = mysql_fetch_row($r);
      $r = fssql_query("select field_id, tag from types_fields where id=$type_id and tag>$tag order by tag limit 1");
      $f = mysql_fetch_row($r);
      if ($f[0]) {
    	  if ($f[1] == $tag) $tag--;
    	  fssql_query("update types_fields set tag=$f[1] where field_id=".(int)$_POST['id']);
    	  fssql_query("update types_fields set tag=$tag where field_id=".(int)$f[0]);
      }
      $res['success'] = true;
    }
    
    if ($_REQUEST['action'] == 'type_create') {
        $rows = json_decode($_POST['rows']);
				$od = ObjectDefinition::create(array(
            'alias'   => $rows->alias, 
            'describ' => $rows->describ, 
            'fixed'   => 0, 
            'handler' => $rows->handler, 
            'plugin'  => $rows->plugin
        ));        
        $res['success'] = true;
        $res['rows'] = array(
            'id'      => $od->id,
            'alias'   => $rows->alias,
            'describ' => $rows->describ,
            'fixed'   => 0,
            'plugin'  => $rows->handler,
            'handler' => $rows->plugin
        );
    }
    
    if ($_REQUEST['action'] == 'type_update') {
        $rows = json_decode($_POST['rows']);
        $od = new ObjectDefinition($rows->id);
        $od->update(array(
            'alias'   => $rows->alias, 
            'describ' => $rows->describ, 
            'fixed'   => 0, 
            'handler' => $rows->handler, 
            'plugin'  => $rows->plugin
        ));
        $res['success'] = true;
    }
    
    if ($_REQUEST['action'] == 'type_delete') {
    
        $rows = json_decode($_POST['rows']);
        
        $od = new ObjectDefinition($rows->id);
        if (!$od->fixed) $od->delete();
        $res['success'] = true;

    }

} catch (Exception_Form $e) {

    $res['success'] = false;
    $res['message'] = '';
    $res['errors'][$e->field] = $e->getMessage();
    
}

echo json_encode($res);



function check_enum($len) {
    $vals = explode(',', stripslashes($len));
    foreach ($vals as $val) {
        $val = trim($val);
        if (!$val) return FALSE;
        if (substr($val, 0, 1) != "'") return FALSE;
        if (substr($val, -1) != "'") return FALSE;
    }
    return TRUE;
}
?>
