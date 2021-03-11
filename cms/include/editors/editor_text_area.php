<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "�����"
 *
 * @version $Id$
 * @copyright 2006 
 **/

function editor_text_area_draw($field_def, $fieldvalue) {
?>
                    Ext.create('Ext.form.field.TextArea',{
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        height: 95,
                        <? if ($field_def['type'] == FIELD_TEXT) : ?>
                        maxLength: <?=$field_def['len']?>,
                        <? endif; ?>
                        value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',addslashes($fieldvalue)))?>'
                    })
<?
    return 100;
}
?>