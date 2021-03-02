<?php
namespace Cetera;
/**
 * Fastsite CMS 3 
 * 
 * AJAX-backend действия с типами материалов
 *
 * @package FastsiteCMS
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
		
		$tag = 0;
		
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
			$tag 		   = $data['tag'];
		}
		else
		{  
			$field_id       = (int)$_REQUEST['field_id'];
			$name          = trim($_REQUEST['name']);
			$describ       = $_REQUEST['describ'];
			$type          = (int)$_REQUEST['type'];
			$len           = (int)$_REQUEST['len'];
			$shw           = isset($_REQUEST['hidden'])?1-(int)$_REQUEST['hidden']:1;
			$required      = isset($_REQUEST['required'])?(int)$_REQUEST['required']:0;
			$default_value = $_REQUEST['default_value'];
			$editor        = (int)$_REQUEST['editor'];
			$editor_user   = $_REQUEST['editor_user'];
			$page          = $_REQUEST['page'];
			$tag           = isset($_REQUEST['tag'])?$_REQUEST['tag']:0;
			
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

					  $len = $application->getConn()->fetchColumn('SELECT id FROM types WHERE alias="material_files"', array(), 0);
            		  if (!$len) {
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
            			  }
                		break;
                    
              	case PSEUDO_FIELD_TAGS: 

                                $len = $application->getConn()->fetchColumn('SELECT id FROM types WHERE alias="material_tags"', [], 0);
              			if (!$len) {	  
              				  $od = ObjectDefinition::create(array(
								  'alias'   => 'material_tags', 
								  'describ' => $translator->_('Ключевые слова'), 
								  'fixed'   => 1
							  ));
							  $len = $od->id;
              			}
                		break;
                    
                case PSEUDO_FIELD_CATOLOGS: 

                    $len = $_REQUEST['catid_l'];
                		break;
                    
            } // switch
        }
                           
		$od = new ObjectDefinition($type_id);
		
		if ($_REQUEST['action'] == 'field_create') {
		
			  $field_id= $od->addField(array(
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
				  'tag'  => $tag,
			  ));                      
		
		}
		
		$res['success'] = true; 
		$res['rows'] = array(
		  'id' => (int)$field_id,
		  'name' => $name, 
		  'type' => $type, 
		  'pseudo_type' => $pseudo_type, 
		  'describ' => $describ, 
		  'describDisplay' => $application->decodeLocaleString($describ), 
		  'len' => $len, 
		  'shw' => $shw, 
		  'required' => $required, 
		  'fixed' => 0, 
		  'editor' => $editor, 
		  'editor_user' => $editor_user, 
		  'default_value' => $default_value,
		  'page' => $page,			
		);

    }
    
    if ($_REQUEST['action'] == 'field_delete') {
        
        $rows = [];
        if ($_REQUEST['id']) {
            $rows = [[
                'id' => $_REQUEST['id']
            ]];
        }
        elseif ($_REQUEST['rows']) {
            $rows = json_decode($_REQUEST['rows'], true);
            if (isset($rows['id'])) {
                $rows = [$rows];
            }
        }
        
        foreach ($rows as $field) {
            $fid = $field['id'];
            $f = $application->getConn()->fetchAssoc('select type, id, name, len, pseudo_type from types_fields where field_id=?', [$fid]);
            if ($f) {            
                if ($f['type'] > 0) {            
                    $od = new ObjectDefinition($f['id']);             
                    // УДАЛЕНИЕ ПОЛЯ
                    if (($f['type']!=FIELD_LINKSET)&&($f['type']!=FIELD_LINKSET2)&&($f['type']!=FIELD_MATSET)) {
                      $application->getConn()->executeQuery("alter table `".$od->table."` drop `".trim($f['name'])."`");
                    } 
                    else {
                      ObjectDefinition::drop_link_table($od->table, $f['name'], $f['type'], $f['len'], $f['id'], $f['pseudo_type']);
                    }
                }            
                $application->getConn()->executeQuery('delete from types_fields where field_id=?', [$fid]);
                $application->getConn()->executeQuery('delete from types_fields_catalogs where field_id=?', [$fid]);
            }
        }
        $res['success'] = true;
    }
	
	if (in_array($_REQUEST['action'],['field_up','field_down'])) {
		list($type_id) = $application->getConn()->fetchArray('select id from types_fields where field_id=?', array($_REQUEST['id']));
		$count = $application->getConn()->fetchColumn('select count(tag) as c from types_fields where id=? group by tag having c>1', [$type_id]);
		if ($count) {
			$application->getConn()->executeQuery('SET @v := 0; UPDATE `types_fields` SET tag = (@v := @v + 10) WHERE id = ? ORDER BY TAG', [$type_id]);
		}
	}
    
    if ($_REQUEST['action'] == 'field_up') {
      list($tag, $type_id) = $application->getConn()->fetchArray('select tag, id from types_fields where field_id=?', array($_REQUEST['id']));
	  $f = $application->getConn()->fetchArray('select field_id, tag from types_fields where id=? and tag<? order by tag desc limit 1', array($type_id, $tag));
      if ($f[0]) {
    	  if ($f[1] == $tag) $tag++;
		  $application->getConn()->executeQuery('update types_fields set tag=? where field_id=?', array($f[1],$_REQUEST['id']));
		  $application->getConn()->executeQuery('update types_fields set tag=? where field_id=?', array($tag,$f[0]));
      }
      $res['success'] = true;
    }
    
    if ($_REQUEST['action'] == 'field_down') {
      list($tag, $type_id) = $application->getConn()->fetchArray('select tag, id from types_fields where field_id=?', array($_REQUEST['id']));
	  $f = $application->getConn()->fetchArray('select field_id, tag from types_fields where id=? and tag>? order by tag limit 1', array($type_id, $tag));
      if ($f[0]) {
    	  if ($f[1] == $tag) $tag--;
		  $application->getConn()->executeQuery('update types_fields set tag=? where field_id=?', array($f[1],$_REQUEST['id']));
		  $application->getConn()->executeQuery('update types_fields set tag=? where field_id=?', array($tag,$f[0]));
      }
      $res['success'] = true;
    }
    
    if ($_REQUEST['action'] == 'type_create') {
        $rows = json_decode($_POST['rows']);
		$od = ObjectDefinition::create(array(
            'alias'   => $rows->alias, 
            'describ' => $rows->describ, 
            'fixed'   => 0, 
        ));        
        $res['success'] = true;
        $res['rows'] = $od->toArray();
    }
    
    if ($_REQUEST['action'] == 'type_update') {
        $rows = json_decode($_POST['rows']);
        $od = new ObjectDefinition($rows->id);
        $od->update(array(
            'alias'   => $rows->alias, 
            'describ' => $rows->describ, 
        ));
        $res['success'] = true;
		$res['rows'] = $od->toArray();
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