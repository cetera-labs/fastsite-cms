<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Пользователь CMS"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_link_user_draw($field_def, $fieldvalue) {

    $r = fssql_query('SELECT IF(name<>"" and name IS NOT NULL, name, login) as autor FROM users WHERE id='.(int)$fieldvalue);
    if (mysql_num_rows($r)) $uname = mysql_result($r,0);
    
?>
                    Ext.create('Cetera.field.User', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=$fieldvalue?>',
                        displayValue: '<?=$uname?>'
                    })
<?
    return 25;
}
?>