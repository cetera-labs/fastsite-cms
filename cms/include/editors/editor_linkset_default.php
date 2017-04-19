<?php
/**
 * Cetera CMS
 * 
 * Default редактор поля "Набор ссылок на материалы"
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

function editor_linkset_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
    if (!$field_def['len']) $field_def['len'] = $idcat;
?>
                    Ext.create('Cetera.field.LinkSet', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        height: 100,
                        from: <?=$field_def['len']?>,
                        store: new Ext.data.ArrayStore({
                            autoDestroy: true,
                            fields: ['id',{name: 'name', mapping: 1}],
                            data: [
<?   
	$r = @fssql_query("select A.alias from types A, dir_data B where A.id = B.typ and B.id=".$field_def['len']);
	list($tbl) = mysql_fetch_row($r);	
	if ($id) {
		$r = fssql_query("select A.id, A.name, A.idcat from ".$tbl." A, ".$math."_".$tbl."_".$field_def['name']." B where A.id = B.dest and B.id=$id order by B.tag");
		$first = 1;
        while ($f = mysql_fetch_row($r)) {
			while ($f[2] != $field_def['len'] && $f[2]) {
		    	$r1 = fssql_query("select A.name, C.data_id from dir_data A, dir_structure B, dir_structure C where B.data_id=$f[2] and A.id=B.data_id and C.lft<B.lft and C.rght>B.rght and C.level=B.level-1");
				$f1 = mysql_fetch_row($r1);
		    	$f[1] = $f1[0].' / '.$f[1];
				$f[2] = $f1[1];				
		  	}
		  	if (!$first) print ',';
		  	$first = 0;
			print "[".(int)$f[0].", '".str_replace("\n",'',addslashes($f[1]))."']";
	  	}
	}
?>
                            ]
                        })
                    })
<?
    return 100;
}