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

function editor_linkset2_default_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
	global $application;
    if (!$field_def['len']) $field_def['len'] = $idcat;
?>
                    Ext.create('Cetera.field.LinkSet2', {
                        fieldLabel: '<?=$field_def['describ']?>',
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        height: 200,
                        from: 0,
                        store: new Ext.data.ArrayStore({
                            autoDestroy: true,
                            fields: ['id',{name: 'name', mapping: 1}],
                            data: [
<? 
	$first = 1;
	if ($fieldvalue) foreach ($fieldvalue as $m) {
		  	if (!$first) print ',';
		  	$first = 0;
			print "['".$m->objectDefinition->id."_".$m->id."', '".str_replace("\n",'',addslashes($m->catalog->getPath()->implode().' / '.$m->name))."']";		
	}
?>
                            ]
                        })
                    })
<?
    return 200;
}