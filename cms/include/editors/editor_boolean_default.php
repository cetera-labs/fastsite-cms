<?php

/**
 * Fastsite CMS
 * 
 * Default редактор поля "Логическое"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_boolean_default_draw($field_def, $fieldvalue) {
	
?>
                    new Ext.form.Checkbox({
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        inputValue: '1',
                        uncheckedValue: '0',
						hideEmptyLabel: false,
						itemId: 'field_<?=$field_def['name']?>',
                        checked: <?=($fieldvalue?'true':'false')?>
                    })
<?
    return 25;
}