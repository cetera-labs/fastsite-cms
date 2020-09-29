<?php

/**
 * Fastsite CMS
 * 
 * Default редактор поля "Набор ссылок на материалы"
 *
 * @version $Id$
 * @copyright 2006 
 **/

function editor_matset_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
	global $application;
?>
                    Ext.create('Cetera.field.MatSet', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        height: 145,
                        mat_type: '<?=$field_def['len']?>',
                        store: new Ext.data.ArrayStore({
                            autoDestroy: true,
                            fields: ['id','name'],
                            data: [
<?	
    $od = new \Cetera\ObjectDefinition($field_def['len']); 
    $tbl = $od->table;
	$not_deleted = ~ MATH_DELETED;
	$r1 = $application->getDbConnection()->query("select id from $tbl where autor=".(int)$user->id." and type & ".MATH_ADDED." > 0");
	while ($f1 = $r1->fetch())
	{
	     $m = \Cetera\Material::getById($f1['id'], 0, $tbl);
         $m->delete();
    }
	$application->getDbConnection()->executeQuery("update $tbl set type = type&$not_deleted where autor=".$user->id." and type & ".MATH_DELETED." > 0");
	if ($id) {
		$r = $application->getDbConnection()->query("select A.id, A.name from $tbl A, ".$math."_".$tbl."_".$field_def['name']." B where A.id = B.dest and B.id=$id order by B.tag");
		$first = 1;
        while ($f = $r->fetch()) {
		  	if (!$first) print ',';
		  	$first = 0;
    		print "[".(int)$f['id'].", '".str_replace("\r",'\r',str_replace("\n",'\n',addslashes($f['name'])))."']";
    	}
	}
?>
                            ]
                        })
                    })
<?
    return 125;
}