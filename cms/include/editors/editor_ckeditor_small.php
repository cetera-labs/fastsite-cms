<?php
function editor_ckeditor_small_init($field_def, $fieldvalue) {
    $fieldvalue = str_replace(chr(0xE2).chr(0x80).chr(0xA8),'',$fieldvalue);
?>
    this.field_<?=$field_def['name']?> = Ext.create('Cetera.field.ck.Small',{
        fieldLabel: '<?=$field_def['describ']?>',
        name: '<?=$field_def['name']?>',
        value: '<?=str_replace("\r",'\r',str_replace("script","scr'+'ipt",str_replace("\n",'\n',addslashes($fieldvalue))))?>'
    });
<?
}

function editor_ckeditor_small_draw($field_def, $fieldvalue) {  
?>
                    this.field_<?=$field_def['name']?>
<?
    return 300;
}

function editor_ckeditor_small_save($field_def) {
?>
    if (this.field_<?=$field_def['name']?>.editor)
        this.field_<?=$field_def['name']?>.setValue(this.field_<?=$field_def['name']?>.editor.getData());
<?
}
?>