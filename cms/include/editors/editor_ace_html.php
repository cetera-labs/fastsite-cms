<?php

/**
 * Fastsite CMS
 * 
 * Default �������� ���� "�����"
 *
 * @version $Id$
 * @copyright 2006 
 **/


 
function editor_ace_html_draw($field_def, $fieldvalue) {
?>
                    Ext.create('Cetera.field.Ace',{
                        fieldLabel: '<?=$field_def['describ']?>',
                        hideLabel: true,
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',addslashes($fieldvalue)))?>',
                        mode: 'html'
                    })
<?
    return -1;
}
?>