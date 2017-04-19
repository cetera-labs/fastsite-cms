<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Форма"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_hidden_draw($field_def, $fieldvalue) {
?>
                    new Ext.form.Hidden({
                        name: '<?=$field_def['name']?>',
                        value: '<?=$fieldvalue?>'
                    })
<?
    return 0;
}
?>

