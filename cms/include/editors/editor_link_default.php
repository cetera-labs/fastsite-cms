<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Ссылка на другой материал"
 *
 * @version $Id: editor_link_default.php,v 1.1 2006/10/16 18:33:15 romanov Exp $
 * @copyright 2006 
 **/
 
function editor_link_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {

	if ($field_def['type'] == FIELD_LINK) {
		if (!$field_def['len']) $field_def['len'] = $idcat;    
		$from_section = $field_def['len'];
		// получаем имя таблицы и id типа материала, на который ссылка
		$section = \Cetera\Catalog::getById($field_def['len']);
		$only = $section->getMaterialsObjectDefinition()->id;
	}
	else {
		$from_section = 0;
		$only = $field_def['len'];
	}
		   
    // Получаем заголовок материала и id раздела в котором он находится
	/*
	$lnk_name = $lnk_idcat = $lnk_cat = '';
	if ($fieldvalue) {		
		if ($a = json_decode($fieldvalue, true) ) {
			$mid = $a['id'];
		}
		else {
			$mid = (int)$fieldvalue;
		}
		
		$material = \Cetera\Material::getById($mid, $only);
		$lnk_name = $material->name;
        $lnk_cat = $material->catalog->getPath()->implode();
	}
	*/
	
?>
                    Ext.create('Cetera.field.Folder', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        //displayValue: '<?=($fieldvalue?$lnk_cat.' / '.$lnk_name:'')?>',
                        value: '<?=addslashes($fieldvalue)?>',
                        from: '<?=$from_section?>',
						only: '<?=$only?>',
                        materials: 1,
                        nocatselect: 1,
                        matsort: 'dat DESC'
                    })
<?
    return 25;
}

