<?php
/**
 * Cetera CMS
 * 
 * Default редактор поля "Набор пользователей"
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/
 
function editor_linkset_user_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
    if (!$field_def['len']) $field_def['len'] = $idcat;
?>
                    Ext.create('Cetera.field.UserSet', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        height: 100,
                        store: new Ext.data.ArrayStore({
                            autoDestroy: true,
                            fields: ['id',{name: 'name', mapping: 1}],
                            data: [
<?
if ($id) {
	  $r = fssql_query("select A.id, A.login  from ".\Cetera\User::TABLE." A, ".$math."_".\Cetera\User::TABLE."_".$field_def['name']." B where A.id = B.dest and B.id=$id order by B.tag");
	  $first = 1;
    while ($f = mysql_fetch_row($r)) {
	  	if (!$first) print ',';
	  	$first = 0;
		  print "[".(int)$f[0].", '".addslashes($f[1])."']";
  	}
}
?>
                            ]
                        })
                    })
<?
    return 100;
}