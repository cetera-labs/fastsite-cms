<?php

/**
 * Cetera CMS
 * 
 * Default редактор поля "Текст"
 *
 * @version $Id$
 * @copyright 2006 
 **/


 
function editor_tags_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
	global $application;

  $od = new \Cetera\ObjectDefinition($field_def['len']); 
  $tbl = $od->table;
	$fieldvalue = '';
	if ($id) {
		$r = $application->getDbConnection()->query("select A.name from $tbl A, ".$math."_".$tbl."_".$field_def['name']." B where A.id = B.dest and B.id=".(int)$id." order by B.tag");
		while ($f = $r->fetch()) {
            if ($fieldvalue) $fieldvalue .= ', '; 
            $fieldvalue .= $f['name'];
        }
	}
?>
                    new Ext.form.TextArea({
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',htmlspecialchars(addslashes($fieldvalue))))?>',
                        height: 95
                    })
<?
    return 95;
}