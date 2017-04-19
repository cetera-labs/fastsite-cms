<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Выбор"
 *
 * @version $Id$
 * @copyright 2006 
 **/

function editor_enum_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
?>
                    new Ext.form.ComboBox({
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        editable: false,
                        store: new Ext.data.SimpleStore({
                            fields: ['value'],
                            data : [
                            <?php
                            $r = fssql_query("SHOW COLUMNS FROM $math LIKE '".$field_def['name']."'");
                            $g = mysql_fetch_row($r);
                            $variant = substr($g[1],6,strlen($g[1])-8);
                            $variants = explode("','",$variant);
                            print "['".implode("'],['",$variants)."']";
                            ?>]
                        }),
                        valueField: 'value',
                        displayField: 'value',
                        queryMode: 'local',
                        triggerAction: 'all',
                        selectOnFocus:true,
                        value: '<?=$fieldvalue?>'
                    })
<?
	return 25;
}
?>