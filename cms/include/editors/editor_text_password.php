<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "�����"
 *
 * @version $Id$
 * @copyright 2006 
 **/

function editor_text_password_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
?>
                    Ext.create('Ext.form.field.Text',{
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=($id?PASSWORD_NOT_CHANGED:'')?>',
                        inputType: 'password'
                    })
<?
    return 25;
}
?>
