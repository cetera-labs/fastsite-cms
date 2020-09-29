<?php
/**
 * Fastsite CMS
 * 
 * Default редактор поля "Набор ссылок на материалы"
 *
 * @package FastsiteCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

function editor_linkset_catalog_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
	global $application;
?>
                    Ext.create('Cetera.field.DirSet', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        height: 100,
                        from: <?=$field_def['len']?>,
                        store: new Ext.data.ArrayStore({
                            autoDestroy: true,
                            fields: ['id','name'],
                            data: [
<?   
	$tbl = \Cetera\Catalog::TABLE;
  	
	if ($id) {
		$r = $application->getDbConnection()->query("select dest from ".$math."_".$tbl."_".$field_def['name']." where id=$id order by tag");
		$first = 1;
		while ($f = $r->fetch()) {
		  $_id = $f['dest'];
		  $name = '';
		  $parent = $_id;
			  while ($parent) {
					$f1 = $application->getDbConnection()->fetchArray("select A.name, C.data_id from dir_data A, dir_structure B, dir_structure C where B.data_id=$parent and A.id=B.data_id and C.lft<B.lft and C.rght>B.rght and C.level=B.level-1");
					if ($name) $name = $f1[0].' / '.$name; else $name = $f1[0];
					$parent = $f1[1];
			  }
			  if (!$first) print ',';
			  $first = 0;
			  print "[".(int)$_id.", '".addslashes($name)."']";
		}
	}
?>
                            ]
                        })
                    })
<?
    return 100;
}