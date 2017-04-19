<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Набор ссылок на материалы"
 *
 * @version $Id$
 * @copyright 2006 
 **/

function editor_file_image_draw($field_def, $fieldvalue) {
?>
                    Ext.create('Cetera.field.Image', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',addslashes($fieldvalue)))?>',
                        height: 150
                    })
<?
    return 150;
}
