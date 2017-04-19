<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Набор ссылок на материалы"
 *
 * @version $Id: editor_linkset_checkbox.php,v 1.2 2006/10/16 19:20:13 romanov Exp $
 * @copyright 2006 
 **/
 
function editor_linkset_checkbox_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
?>
                    Ext.create('Cetera.field.CheckList',{
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        height: 100,
                        store: new Ext.data.ArrayStore({
                            autoDestroy: true,
                            fields: ['id','name','selected'],
                            data: [
<?
	$r = @fssql_query("select A.alias, A.id from types A, dir_data B where A.id = B.typ and B.id=".$field_def['len']);
	$tbl = mysql_result($r,0, 'alias');
	$type = mysql_result($r,0, 'id');
	
	$linked = array();
	if ($id) {
		$r = fssql_query("SELECT A.id FROM ".$tbl." A, ".$math."_".$tbl."_".$field_def['name']." B WHERE A.id = B.dest and B.id=$id");
		while ($f = mysql_fetch_row($r)) $linked[] = $f[0];
	}	

	$sql = '
		SELECT A.name, A.id, B.level 
		FROM dir_structure C 
		LEFT JOIN dir_structure B ON (B.lft>=C.lft and B.rght<=C.rght)
		LEFT JOIN dir_data A ON (B.data_id=A.id)
		WHERE C.data_id='.$field_def['len'].' and A.typ='.$type.'
		ORDER BY B.lft';
	$r = fssql_query($sql);
	$first = 1;
	while($f = mysql_fetch_assoc($r)) {    
		$sql = 'SELECT id,name FROM '.$tbl.' WHERE idcat='.$f['id'].' and type&'.MATH_PUBLISHED.'=1 ORDER BY name';
		$r1 = fssql_query($sql);	
		while($f1 = mysql_fetch_assoc($r1)) {
		  	if (!$first) print ',';
		  	$first = 0;
			print "[".(int)$f1['id'].", '".str_replace("\n",'',addslashes($f1['name']))."',".(in_array($f1['id'], $linked)?' true':' false')."]";
		}
	} // while
?>
                            ]
                        })
                    })
<?
    return 100;
}