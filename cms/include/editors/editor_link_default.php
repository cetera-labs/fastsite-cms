<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "������ �� ������ ��������"
 *
 * @version $Id: editor_link_default.php,v 1.1 2006/10/16 18:33:15 romanov Exp $
 * @copyright 2006 
 **/
 
function editor_link_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {

	try {

	if ($field_def['type'] == FIELD_LINK) {
		if (!$field_def['len']) $field_def['len'] = $idcat;    
		$from_section = $field_def['len'];
		$section = \Cetera\Catalog::getById($field_def['len']);
		$only = $section->getMaterialsObjectDefinition()->id;
	}
	else {
		$from_section = 0;
		$only = $field_def['len'];
	}
?>
                    Ext.create('Cetera.field.Folder', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=addslashes($fieldvalue)?>',
                        from: '<?=$from_section?>',
						only: '<?=$only?>',
                        materials: 1,
                        nocatselect: 1,
                        matsort: 'dat DESC'
                    })
<?
	}
	catch (\Exception $e) {
?>
                    Ext.create('Ext.form.field.Display', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        value: '<?=addslashes($fieldvalue)?>'
                    })
<?		
	}
	
    return 25;
}

