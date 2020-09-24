<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "����� �����"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_double_default_draw($field_def, $fieldvalue) {
?>
                    new Ext.form.NumberField({
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',addslashes($fieldvalue)))?>'
                    })
<?
    return 25;
}
?>
