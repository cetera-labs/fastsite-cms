<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Текст"
 *
 * @version $Id$
 * @copyright 2006 
 **/


 
function editor_visual_template_draw($field_def, $fieldvalue) {
?>
                    Ext.create('Cetera.field.VisualTemplate',{
                        fieldLabel: '<?=$field_def['describ']?>',
                        hideLabel: true,
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',addslashes($fieldvalue)))?>',
                    })
<?
    return -1;
}
?>