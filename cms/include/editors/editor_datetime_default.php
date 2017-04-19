<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Логическое"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_datetime_default_draw($field_def, $fieldvalue) {
?>
                    new Ext.form.DateField({
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=$fieldvalue?>',
                        format: 'Y-m-d H:i:s',
                        listeners : {
                             scope: this,
                             'select': function(f, d){
                                var now = new Date();
                                d.setHours(now.getHours());
                                d.setSeconds(now.getSeconds());
                                d.setMinutes(now.getMinutes());
                                f.setValue(d);
                             }
                        }
                    })
<?
    return 25;
}
?>