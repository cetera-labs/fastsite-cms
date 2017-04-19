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

function editor_linkset_link_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
	$od = \Cetera\ObjectDefinition::findById($field_def['id']);
?>
                    Ext.create('Cetera.field.LinkSet_Link', {
                        name: '<?=$od->getAlias()?>_<?=$field_def['name']?>',
						mat_type: <?=$field_def['id']?>,
						mat_filter: '<?=$field_def['name'].'='.$id?>'
                    })
<?
    return -1;
}