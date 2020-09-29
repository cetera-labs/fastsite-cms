<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "������������ CMS"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_link_user_draw($field_def, $fieldvalue) {
	global $application;

    $uname = $application->getDbConnection()->fetchColumn('SELECT IF(name<>"" and name IS NOT NULL, name, login) as autor FROM users WHERE id='.(int)$fieldvalue);
    
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