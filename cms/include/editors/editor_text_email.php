<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Текст"
 *
 * @version $Id$
 * @copyright 2006 
 **/

function editor_text_email_draw($field_def, $fieldvalue) {
?>
                    new Ext.form.TextField({
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',addslashes($fieldvalue)))?>',
                        regex: /^[A-Z0-9\._%-]+@[A-Z0-9\.-]+\.[A-Z]{2,4}$/i
                    })
<?
    return 25;
}
?>
