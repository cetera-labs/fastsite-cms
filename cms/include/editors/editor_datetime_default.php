<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "����������"
 *
 * @version $Id$
 * @copyright 2006 
 **/
 
function editor_datetime_default_draw($field_def, $fieldvalue) {
?>
                    Ext.create('Ext.form.field.Date',{
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