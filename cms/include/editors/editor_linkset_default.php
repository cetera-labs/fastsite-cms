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

function editor_linkset_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
	global $application;
    if (!$field_def['len']) $field_def['len'] = $idcat;
?>
                    Ext.create('Cetera.field.LinkSet', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        height: 150,
                        from: <?=$field_def['len']?>,
                        store: new Ext.data.ArrayStore({
                            autoDestroy: true,
                            fields: ['id',{name: 'name', mapping: 1}],
                            data: [
<? 
	$tbl = $application->getDbConnection()->fetchColumn("select A.alias from types A, dir_data B where A.id = B.typ and B.id=".$field_def['len']);
	
	if ($id) {
		$r = $application->getDbConnection()->query("select A.id, A.name, A.idcat from ".$tbl." A, ".$math."_".$tbl."_".$field_def['name']." B where A.id = B.dest and B.id=$id order by B.tag");
		$first = 1;
        while ($f = $r->fetch()) {
			while ($f['idcat'] != $field_def['len'] && $f['idcat']) {
		    	$f1 = $application->getDbConnection()->fetchArray("select A.name, C.data_id from dir_data A, dir_structure B, dir_structure C where B.data_id=".$f['idcat']." and A.id=B.data_id and C.lft<B.lft and C.rght>B.rght and C.level=B.level-1");
		    	$f['name'] = $f1['name'].' / '.$f['name'];
				$f['idcat'] = $f1['data_id'];				
		  	}
		  	if (!$first) print ',';
		  	$first = 0;
			print "[".(int)$f['id'].", '".str_replace("\n",'',addslashes($f['name']))."']";
	  	}
	}
?>
                            ]
                        })
                    })
<?
    return 150;
}