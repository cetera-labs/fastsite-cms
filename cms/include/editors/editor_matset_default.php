<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Набор ссылок на материалы"
 *
 * @version $Id$
 * @copyright 2006 
 **/

function editor_matset_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
?>
                    Ext.create('Cetera.field.MatSet', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=$fieldvalue?>',
                        height: 125,
                        mat_type: '<?=$field_def['len']?>',
                        store: new Ext.data.ArrayStore({
                            autoDestroy: true,
                            fields: ['id','name'],
                            data: [
<?	
    $od = new \Cetera\ObjectDefinition($field_def['len']); 
    $tbl = $od->table;
	$not_deleted = ~ MATH_DELETED;
	$r1 = fssql_query("select id from $tbl where autor=".(int)$user->id." and type & ".MATH_ADDED." > 0");
	while ($f1 = mysql_fetch_row($r1))
	{
	     $m = \Cetera\Material::getById($f1[0], 0, $tbl);
         $m->delete();
    }
	fssql_query("update $tbl set type = type&$not_deleted where autor=".$user->id." and type & ".MATH_DELETED." > 0");
	if ($id) {
		$r = fssql_query("select A.id, A.name from $tbl A, ".$math."_".$tbl."_".$field_def['name']." B where A.id = B.dest and B.id=$id order by B.tag");
		$first = 1;
        while ($f = mysql_fetch_row($r)) {
		  	if (!$first) print ',';
		  	$first = 0;
    		print "[".(int)$f[0].", '".str_replace("\r",'\r',str_replace("\n",'\n',addslashes($f[1])))."']";
    	}
	}
?>
                            ]
                        })
                    })
<?
    return 125;
}