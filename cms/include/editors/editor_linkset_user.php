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
	global $application;
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
	  $r = $application->getDbConnection()->query("select A.id, A.login  from ".\Cetera\User::TABLE." A, ".$math."_".\Cetera\User::TABLE."_".$field_def['name']." B where A.id = B.dest and B.id=$id order by B.tag");
	  $first = 1;
      while ($f = $r->fetch()) {
	  	  if (!$first) print ',';
	  	  $first = 0;
		  print "[".(int)$f['id'].", '".addslashes($f['login'])."']";
  	  }
}
?>
                            ]
                        })
                    })
<?
    return 100;
}