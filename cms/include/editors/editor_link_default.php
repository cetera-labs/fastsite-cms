<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Ссылка на другой материал"
 *
 * @version $Id: editor_link_default.php,v 1.1 2006/10/16 18:33:15 romanov Exp $
 * @copyright 2006 
 **/
 
function editor_link_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {

    if (!$field_def['len']) $field_def['len'] = $idcat;
    
	// получаем имя таблицы и id типа материала, на который ссылка
	if (!$field_def['len']) $field_def['len'] = $idcat;
	$r = fssql_query("select A.alias,A.id from types A, dir_data B where A.id = B.typ and B.id=".$field_def['len']);
    list($tbl,$only) = mysql_fetch_row($r);
		   
    // Получаем заголовок материала и id раздела в котором он находится
	$lnk_name = $lnk_idcat = $lnk_cat = '';
	if ($fieldvalue && $tbl) {
		
		if ($a = json_decode($fieldvalue, true) ) {
			$mid = $a['id'];
		}
		else {
			$mid = (int)$fieldvalue;
		}
		
		$r = fssql_query("select name,idcat from ".$tbl." where id=".$mid);
		list($lnk_name,$lnk_idcat) = mysql_fetch_row($r);	
		if ($lnk_idcat) {
		    $c = Cetera\Catalog::getById($lnk_idcat);
            if ($c) $lnk_cat = $c->getPath()->implode();
        }
	}
	
?>
                    Ext.create('Cetera.field.Folder', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        displayValue: '<?=($fieldvalue?$lnk_cat.' / '.$lnk_name:'')?>',
                        value: '<?=addslashes($fieldvalue)?>',
                        from: '<?=$field_def['len']?>',
                        materials: 1,
                        nocatselect: 1,
                        matsort: 'dat DESC'
                    })
<?
    return 25;
}

