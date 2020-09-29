<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "����������"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_file_default_draw($field_def, $fieldvalue) {
?>   
                    Ext.create('Cetera.field.File', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',addslashes($fieldvalue)))?>'
                    })
<?
    return 25;
}
?>

