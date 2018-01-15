<?php

/**
 * Cetera CMS
 * 
 * Default редактор пол€ "—сылка на другой материал"
 *
 * @version $Id: editor_link_default.php,v 1.1 2006/10/16 18:33:15 romanov Exp $
 * @copyright 2006 
 **/
function editor_link_catalog_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {	
?>
                    Ext.create('Cetera.field.Folder', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=addslashes($fieldvalue)?>',
                        from: 0,
                        materials: 0,
                    })
<?
    return 25;
}

