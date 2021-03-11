<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "�����"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_hidden_draw($field_def, $fieldvalue) {
?>
                    Ext.create('Ext.form.field.Hidden',{
                        name: '<?=$field_def['name']?>',
                        value: '<?=$fieldvalue?>'
                    })
<?
    return 0;
}
?>

