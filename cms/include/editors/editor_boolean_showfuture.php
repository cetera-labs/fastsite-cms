<?php

/**
 * Fastsite CMS
 * 
 * Default редактор поля "Логическое"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_boolean_showfuture_draw($field_def, $fieldvalue) {
	
?>
                    Ext.create('Ext.form.field.Checkbox',{
						boxLabel: 'показать на сайте',
                        name: '<?=$field_def['name']?>',
                        inputValue: '1',
                        uncheckedValue: '0',
						hideEmptyLabel: false,
						itemId: 'field_<?=$field_def['name']?>',
						hidden: 1,
                        checked: <?=($fieldvalue?'true':'false')?>
                    })
<?
    return 25;
}