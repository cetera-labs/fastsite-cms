<?php
namespace Cetera;

include('common_bo.php');

try {
    $section_id = Util::get('section_id', TRUE);
    $duplicate = Util::get('duplicate', TRUE);
    $id = Util::get('id', TRUE);
    
    if (isset($_REQUEST['od_id']) && $_REQUEST['od_id']) {
        $objectDefinition = ObjectDefinition::findById($_REQUEST['od_id']);
    }
    else {
        $objectDefinition = ObjectDefinition::findByAlias($_REQUEST['od_alias']);
    } 
    
    $data = [
        'success' => true,
        'objectDefinition' => [
            'id' => $objectDefinition->id,
            'alias' => $objectDefinition->alias,
        ],
    ];     
   
    if ($id) {
        
	    $r = $application->getConn()->fetchAssoc('SELECT user_id FROM `lock` WHERE  dat >= NOW()-INTERVAL 10 SECOND and material_id=? and type_id=?', [$id, $objectDefinition->id] );
        if ($r) throw new Exception\CMS( $translator->_('Материал заблокирован другим пользователем.'), false, true );        
        
        // новый материал по шаблону
        if ($duplicate) {
          $fields = $application->getConn()->fetchAssoc("SELECT * from $math WHERE id=?", array($id));
          $id = null;
          $fields['alias'] = '';
          $fields['idcat'] = $section_id;
          $material = DynamicFieldsObject::fetch($fields, $objectDefinition->id);
        } 
        else {

          $material = DynamicFieldsObject::getByIdType($id, $objectDefinition->id);
          if ($section_id != CATALOG_VIRTUAL_USERS) $section_id  = $material->idcat;
          $fields = [];
          
        }
    }
    
    if ($application->getVar('editor.autoflow'))
        $page_height = isset($_REQUEST['height'])?$_REQUEST['height']-120:PAGE_HEIGHT;
        else $page_height = -1; 

    $objectRenderer = new ObjectRenderer($objectDefinition, $section_id, $id, $page_height, $translator->_('Свойства'));
    
    if (!$id) {
    
        // Новый материал. Заполняем поля default значениями.
        foreach ($objectRenderer->fields_def as $name => $value) 
			if (!isset($fields[$name])) $fields[$name] = $value['default_value'];	
        $fields['idcat'] = $section_id;
        
        if ($section_id == CATALOG_VIRTUAL_HIDDEN)
            $fields['alias'] = 'hidden';       
    
       $material = DynamicFieldsObject::fetch($fields, $objectDefinition);
       
    }   
    
    if ($section_id > 0) {
		$section = Section::getById( $section_id );		
    	$cat_type = $application->getConn()->fetchColumn("select type from dir_data where id=?", [$section_id]);
    } else {
        $cat_type = 0;
    }
       
    $others = $user->allowCat(PERM_CAT_ALL_MAT, $section_id); // Работа с материалами других авторов
    $right_publish = $user->allowCat(PERM_CAT_MAT_PUB, $section_id); // Публикация материалов     

    $data['permissions'] = [
        'others' => $others,
        'publish' => $right_publish,
    ];

    $data['fields'] = $fields;
    
    if ($section_id != CATALOG_VIRTUAL_USERS)  {
        if ($cat_type & Catalog::AUTOALIAS) 
            $objectRenderer->fields_def['alias']['required'] = 0; 
            else $objectRenderer->fields_def['alias']['required'] = 1;
			
        $objectRenderer->fields_def['alias']['name'] = 'alias';
        $objectRenderer->fields_def['dat']['name'] = 'dat';
        $objectRenderer->fields_def['name']['name'] = 'name';
		
        if (!isset($objectRenderer->fields_def['alias']['describ']))$objectRenderer->fields_def['alias']['describ'] = $translator->_('Alias');
        if (!isset($objectRenderer->fields_def['dat']['describ']))  $objectRenderer->fields_def['dat']['describ']   = $translator->_('Дата создания');
        if (!isset($objectRenderer->fields_def['name']['describ'])) $objectRenderer->fields_def['name']['describ']  = $translator->_('Заголовок');
		
		if (!$material->autor) $material->autor = $user->id;
    }
    	
    $objectRenderer->setObject($material);  

    if ($section_id != CATALOG_VIRTUAL_USERS) {
    
        $objectRenderer->addToPage($objectRenderer->fields_def['name']);
        unset($objectRenderer->fields_def['name']);
        
        if ($section_id >= 0)
            $objectRenderer->fields_def['alias']['editor_str']='editor_text_alias';
            else $objectRenderer->fields_def['alias']['editor_str']='editor_hidden';
                   
        $objectRenderer->addToPage($objectRenderer->fields_def['alias']);
        unset($objectRenderer->fields_def['alias']);
              
        $objectRenderer->fields_def['dat']['editor_str']='editor_datetime_pubdate';   
        $objectRenderer->addToPage($objectRenderer->fields_def['dat']);
        unset($objectRenderer->fields_def['dat']);
        if ($section_id > 0) {
            $objectRenderer->addToPage(array(
                'editor_str' => 'editor_boolean_showfuture',
                'shw'        => 1,
                'type'       => FIELD_BOOLEAN,
                'name'       => 'show_future',
            ));
        }					
               
    }

    $data['init'] = $objectRenderer->initalizeFields(true);
    $data['tabs'] = $objectRenderer->toArray();
    
    echo json_encode($data);
    
} catch (\Exception $e) {

    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'rows'    => false
    ));

}
