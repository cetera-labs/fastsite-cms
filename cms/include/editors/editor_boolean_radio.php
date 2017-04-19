<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Логическое"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_boolean_radio_draw($field_def, $fieldvalue) {
    ?>
    new Ext.form.RadioGroup({
        fieldLabel: '<?=$field_def['describ']?>',
        width: 100,
        items: [
            new Ext.form.Radio({ boxLabel: 'да', name: '<?=$field_def['name']?>', inputValue: 1<?=($fieldvalue?', checked: true':'')?> }),
            new Ext.form.Radio({ boxLabel: 'нет', name: '<?=$field_def['name']?>', inputValue: 0<?=(!$fieldvalue?', checked: true':'')?> })
        ]
    })
    <?
	return 25;
}