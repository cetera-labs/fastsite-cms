<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "��������"
 *
 * @version $Id: editor_link_default.php,v 1.1 2006/10/16 18:33:15 romanov Exp $
 * @copyright 2006 
 **/
 
function editor_material_default_draw($field_def, $fieldvalue) {
?>
                    Ext.create('Cetera.field.Material', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
						mat_type: '<?=$field_def['len']?>',
                        value: '<?=$fieldvalue?>'
                    })
<?
    return 25;
}
?>
