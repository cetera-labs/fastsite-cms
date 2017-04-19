<?php
function editor_matset_rich_draw($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
    $type = $field_def['len'];
    $od = new Cetera\ObjectDefinition($type);

    $fields = "'id','".implode("','", array_keys($od->getFields()))."'";
    $values = array();
    
    if (is_array($fieldvalue) || is_object($fieldvalue))
        foreach($fieldvalue as $v) {
            $data = array('id' => $v->id);
            foreach (array_keys($od->getFields()) as $field)
                $data[$field] = (string)$v->$field;
            $values[] = $data;
        }
    
?>
                    Ext.create('Cetera.field.RichMatSet',{
                        name: '<?=$field_def['name']?>',
                        allowBlank:<?=($field_def['required']?'false':'true')?>,
                        mat_type: '<?=$field_def['len']?>',
                        store: new Ext.data.ArrayStore({
                            autoDestroy: true,
                            fields: [<?php echo $fields; ?>],
                            data: [
                            <?php foreach ($values as $i => $row) :?>
                                <?php if ($i) : ?>
                                ,
                                <?php endif; $j=0; ?>
                                [<?php foreach ($row as $v) :?><?php if ($j) : ?>, <?php endif; $j=1; ?> '<?php echo str_replace("\r",'\r',str_replace("\n",'\n',addslashes($v))); ?>'<?php endforeach; ?>]
                            <?php endforeach; ?>
                            ]
                        })
                    })
<?
    return -1;
}


function editor_matset_rich_init($field_def, $fieldvalue, $id = false, $idcat = false, $math = false, $user = false) {
    $type = $field_def['len'];
    $od = new Cetera\ObjectDefinition($type);
    $or = new Cetera\ObjectRenderer($od);  
    unset($or->fields_def['alias']);  
    unset($or->fields_def['autor']);

?>
Cetera.RichMatsetMaterial<?=$type?> = Ext.define('Cetera.RichMatsetMaterial<?=$type?>', {

    extend: 'Cetera.field.RichMatsetMaterialAbstract',
    
    initComponent : function(){
        this.items = [
            <?php $or->renderFields(0); ?>
        ];
        this.callParent(); 
    }
    
});
<?
}