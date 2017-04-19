<?php
namespace Cetera;
header('Content-Type: application/javascript; charset=UTF-8');
/**
 * Cetera CMS 3 
 * 
 * Интерфейс редактирования материала  
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/ 

try {  

    include('common_bo.php');
    
    //  id материала
    $id = Util::get('id', TRUE);
    
    // id раздела
    $idcat = Util::get('idcat', TRUE);
    
    // тип материала
    $type = Util::get('type');
	       
    // новый материал по шаблону
    $duplicate = Util::get('duplicate', TRUE);
    if ($id && $idcat > 0) $duplicate = 1;
    
    // редактирование пользователя
    if ($idcat == CATALOG_VIRTUAL_USERS) $type = User::TYPE;
        
    $hidden = 0;
    
	if ($type && !(int)$type)
	{
		$objectDefinition = ObjectDefinition::findByAlias($type);
		$type = $objectDefinition->id;
	}
	else
	{
		$objectDefinition = ObjectDefinition::findById($type);
	}
	
    if ($type && $id) {        
        $r = fssql_query('SELECT user_id FROM `lock` WHERE  dat >= NOW()-INTERVAL 10 SECOND and material_id='.$id.' and type_id='.$type);
        if (mysql_num_rows($r)) throw new Exception\CMS( $translator->_('Материал заблокирован другим пользователем.') );
    }	
	
	$math = $objectDefinition->table;
	$plugin = $objectDefinition->plugin;
    
    include_once('editors/editor_datetime_pubdate.php');
    include_once('editors/editor_text_default.php');
	include_once('editors/editor_boolean_showfuture.php');
    include_once('editors/editor_text_alias.php');
    include_once('editors/editor_link_user.php');
    include_once('editors/editor_hidden.php');
	include_once('editors/editor_integer_default.php');
    
    if ($application->getVar('editor.autoflow'))
        $page_height = isset($_GET['height'])?$_GET['height']-120:PAGE_HEIGHT;
        else $page_height = -1;
      
    
    if ($id) 
	{
      
      // новый материал по шаблону
      if ($duplicate) {
      
          $r = fssql_query("SELECT * from $math WHERE id=$id");
          $fields = mysql_fetch_assoc($r);      
          $id = null;
      	  $fields['alias'] = '';
          $fields['idcat'] = $idcat;
          
          $material = DynamicFieldsObject::fetch($fields, $type);
          
      } else {
      
          $material = DynamicFieldsObject::getByIdType($id, $type);
          if ($idcat != CATALOG_VIRTUAL_USERS) $idcat  = $material->idcat;
		  $fields = array();
          
      }
      
    }
    
    $objectRenderer = new ObjectRenderer($objectDefinition, $idcat, $id, $page_height, $translator->_('Свойства'));      
    
    if (!$id) {
    
        // Новый материал. Заполняем поля default значениями.
        foreach ($objectRenderer->fields_def as $name => $value) 
			if (!isset($fields[$name])) $fields[$name] = $value['default_value'];	
        if (!isset($fields['autor'])) $fields['autor'] = $user->id;
        $fields['idcat'] = $idcat;
        
        if ($idcat == CATALOG_VIRTUAL_HIDDEN)
            $fields['alias'] = 'hidden';       
    
       $material = DynamicFieldsObject::fetch($fields, $type);
       
    }
    
    if ($idcat > 0) {
    	  $r	  = fssql_query("select type from dir_data where id=$idcat");
    	  $cat_type = mysql_result($r,0);
    } else {
        $cat_type = 0;
    }
       
    $others = $user->allowCat(PERM_CAT_ALL_MAT, $idcat); // Работа с материалами других авторов
    $right_publish = $user->allowCat(PERM_CAT_MAT_PUB, $idcat); // Публикация материалов 
    
    
    if ($idcat != CATALOG_VIRTUAL_USERS) 
	{
        if ($cat_type & Catalog::AUTOALIAS) 
            $objectRenderer->fields_def['alias']['required'] = 0; 
            else $objectRenderer->fields_def['alias']['required'] = 1;
			
        $objectRenderer->fields_def['alias']['name'] = 'alias';
        $objectRenderer->fields_def['autor']['required'] = 0;
        $objectRenderer->fields_def['autor']['name'] = 'autor';
        $objectRenderer->fields_def['dat']['name'] = 'dat';
        $objectRenderer->fields_def['name']['name'] = 'name';
		
        if (!isset($objectRenderer->fields_def['alias']['describ']))$objectRenderer->fields_def['alias']['describ'] = $translator->_('Alias');
        if (!isset($objectRenderer->fields_def['autor']['describ']))$objectRenderer->fields_def['autor']['describ'] = $translator->_('Автор');
        if (!isset($objectRenderer->fields_def['dat']['describ']))  $objectRenderer->fields_def['dat']['describ']   = $translator->_('Дата создания');
        if (!isset($objectRenderer->fields_def['name']['describ'])) $objectRenderer->fields_def['name']['describ']  = $translator->_('Заголовок');
    }
    
    $objectRenderer->setObject($material);  
  
    
    ?>
Ext.form.Basic.prototype.findInvalid = function() {
    var me = this,
        invalid;
    Ext.suspendLayouts();
    invalid = me.getFields().filterBy(function(field) {
        var preventMark = field.preventMark, isValid;
        field.preventMark = true;
        isValid = field.isValid() && !field.hasActiveError();
        field.preventMark = preventMark;
        return !isValid;
    });
	
    Ext.resumeLayouts(true);
    return invalid;
};

Ext.override(Ext.Component, {
    ensureVisible: function(stopAt) {
        var p;
        this.ownerCt.bubble(function(c) {
            if (p = c.ownerCt) {
                if (p instanceof Ext.TabPanel) {
                    p.setActiveTab(c);
                } else if (p.layout.setActiveItem) {
                    p.layout.setActiveItem(c);
                }
            }
            return (c !== stopAt);
        });
        this.el.scrollIntoView(this.el.up(':scrollable'));
        return this;
    }
});

Ext.DomQuery.pseudos.scrollable = function(c, t) {
    var r = [], ri = -1;
    for(var i = 0, ci; ci = c[i]; i++){
        var o = ci.style.overflow;
        if(o=='auto'||o=='scroll') {
            //if (ci.scrollHeight < Ext.fly(ci).getHeight(true)) 
				r[++ri] = ci;
        }
    }
    return r;
};
	
    Ext.define('MaterialEditorBase<?=$type?>', {
        extend: 'Ext.FormPanel',
    
        layout: 'fit',
        
        fieldDefaults: {
            labelAlign: 'right',
            labelWidth: <?=LABEL_WIDTH?>
        },
        
        bodyStyle: 'background: none',
        border   : false,
        pollForChanges: true,
        
        timeout : 100,

        initComponent : function() {
            
            this.task = Ext.TaskManager.start({
                 run: function() {
                 
                    if (this.saveParams.id > 0) {
                 
                        Ext.Ajax.request({
                            url: '/<?=CMS_DIR?>/include/action_materials.php?action=lock&mat_id=' + this.saveParams.id + '&type=<?=(int)$type?>',
                            failure: function(){
                            },
                            scope: this
                        });
                    
                    }
                 
                 },
                 scope: this,
                 interval: 10000
            });
        
            <?if ($idcat == CATALOG_VIRTUAL_USERS) {?>
                this.mGrid = new Ext.grid.GridPanel({
                    store: new Ext.data.JsonStore({
                        autoLoad: true,
                        fields: ['id', 'name'],
                        proxy: {
                            type: 'ajax',
                            url: 'include/data_groups.php?member=<?=(int)$id?>',
							simpleSortMode: true,
							reader: {
								type: 'json',
								root: 'rows'
							}							
                        }
                    }),
                    columns          : [
                  		{width: 20, renderer: function(v, m) { m.css = 'icon-users'; } },
                  		{dataIndex: 'name', flex: 1}
                  	],
                    viewConfig: {
                        plugins: {
                            ptype: 'gridviewdragdrop',
                            dragGroup: 'memberGridDDGroup',
                            dropGroup: 'allGridDDGroup'
                        }
                    },
                    anchor: '100% 50%',
                    hideHeaders      : true,
                    title            : '<?=$translator->_('Состоит в')?>',
                    listeners        : {
                        itemdblclick : {
                            fn: function(t, record) {
                                this.mGrid.store.remove(record);
                                this.aGrid.store.add(record);
                            },
                            scope: this       
                        }
                    } 
                });
            
                this.aGrid = new Ext.grid.GridPanel({
                    store: Ext.create('Ext.data.JsonStore',{
                        autoLoad: true,
                        fields: ['id', 'name'],
                        proxy: {
                            type: 'ajax',
                            url: 'include/data_groups.php?avail=<?=(int)$id?>',
							simpleSortMode: true,
							reader: {
								type: 'json',
								root: 'rows'
							}							
                        }
                    }),
                    columns          : [
                  		{width: 20, renderer: function(v, m) { m.css = 'icon-users'; } },
                  		{dataIndex: 'name', flex: 1}
                  	],
                    viewConfig: {
                        plugins: {
                            ptype: 'gridviewdragdrop',
                            dragGroup: 'allGridDDGroup',
                            dropGroup: 'memberGridDDGroup'
                        }
                    },
                    margin: '5 0 0 0',
                    anchor: '100% 50%',
                    hideHeaders      : true,
                    title            : '<?=$translator->_('Группы')?>',
                    listeners        : {
                        itemdblclick : {
                            fn: function(t, record) {
                                this.aGrid.store.remove(record);
                                this.mGrid.store.add(record);
                            },
                            scope: this    
                        }
                    }
                });  
            <?}?>
            
            <?php $objectRenderer->initalizeFields(); ?>
            
            this.tabPanel = new Ext.TabPanel({
                activeTab : 0,
                border    : false,
                bodyStyle :'background: none',
                deferredRender: false,
                defaults  :{
                    height: this.win.height-105, 
                    bodyStyle:'padding:10px'
                }, 
                items:[
                <?
                if ($idcat != CATALOG_VIRTUAL_USERS)
				{
                
					$objectRenderer->addToPage($objectRenderer->fields_def['name']);
					unset($objectRenderer->fields_def['name']);
                    
                    if ($idcat >= 0)
						$objectRenderer->fields_def['alias']['editor_str']='editor_text_alias';
                    	else $objectRenderer->fields_def['alias']['editor_str']='editor_hidden';
							   
                    $objectRenderer->addToPage($objectRenderer->fields_def['alias']);
                    unset($objectRenderer->fields_def['alias']);
                    	   
                    if ($others && (int)$idcat > 0)
						$objectRenderer->fields_def['autor']['editor_str']='editor_link_user';
                    	else 
                            $objectRenderer->fields_def['autor']['editor_str'] = 'editor_hidden'; 
							
                    $objectRenderer->addToPage($objectRenderer->fields_def['autor']);
                    unset($objectRenderer->fields_def['autor']);
                    	  
                    $objectRenderer->fields_def['dat']['editor_str']='editor_datetime_pubdate';   
					$objectRenderer->addToPage($objectRenderer->fields_def['dat']);
                    unset($objectRenderer->fields_def['dat']);
					if ($idcat > 0)
					{
						$objectRenderer->addToPage(array(
							'editor_str' => 'editor_boolean_showfuture',
							'shw'        => 1,
							'type'       => FIELD_BOOLEAN,
							'name'       => 'show_future',
						));
					}					
                    	   
                }
                $objectRenderer->renderFields();
                ?>
                     
                <?php if ($idcat == CATALOG_VIRTUAL_USERS) : ?>
                    ,{
                        title:'<?=htmlspecialchars($translator->_('Членство в группах'))?>',
                        layout:'anchor',
                        defaults: {anchor: '0'},
                        border    : false,
                        bodyBorder: false,
                        bodyStyle:'background: none; padding: 5px',
                        items: [this.mGrid, this.aGrid]
                	   }
                <?php endif; ?> 
        
                ]
                
            });
            this.items = this.tabPanel;
                       
            <?if ($idcat >= 0 && !$_REQUEST['modal']) {?> 
                        
                this.savebut =  Ext.create('Ext.Button', {
                    text: '<?=$translator->_('Сохранить')?>',
                    handler: this.save,
                    scope: this
                });
            
            	  <?if ($right_publish) {?>
            	
                this.publishbut = Ext.create('Ext.Button', {
					id: 'publish_button',
                    text: '<?=$translator->_('Сохранить и опубликовать')?>',
                    handler: function() { 
						this.save_publish(0);
					},
                    scope: this
                });
            	  <?}?>
              
                this.previewbut = Ext.create('Ext.Button', {
                    text: '<?=$translator->_('Предпросмотр')?>',
                    handler: this.save_preview,
                    scope: this
                });
                
                this.buttons = [this.savebut, <?if ($right_publish) {?>this.publishbut, <?}?>this.previewbut];
            	
            <?} else {?>
            
                this.okbut = Ext.create('Ext.Button', {
                    text: '<?=$translator->_('OK')?>',
                    handler: function() { 
						this.save_publish(1);
					},
                    scope: this
                });
                
                this.buttons = [
                    this.okbut,
                    {
                        text: '<?=$translator->_('Отмена')?>',
                        scope: this,
                        handler: function() { 
                            this.win.returnValue = false;
                            this.win.close(); 
                        }
                    }
                ];
                
            <?}?>
            
            this.callParent();
            
        },
        
        show : function() {
               
            this.win.on('beforeclose', function(){
                if (this.task) Ext.TaskManager.destroy(this.task);
                Ext.Ajax.request({
                    url: '/<?=CMS_DIR?>/include/action_materials.php?action=clear_lock&mat_id=' + this.saveParams.id + '&type=<?=(int)$type?>'
                });
                this.destroy();
            }, this);
            
            
            this.win.add(this);
            this.win.doLayout();
            this.win.show();  
            if (this.win.getEl()) 
                this.win.getEl().child('div > table.loading').setStyle('display','none');            
            this.win.materialForm = this;      
            this.callParent();
        },
        
        save: function(){
            this.saveAction(0,0);
        },
        
        save_publish: function(close){
            this.saveAction(1,0,close);
        },
        
        save_preview: function(){
            this.saveAction(0,1);
        },
                
        saveParams: {
            table: '<?=$math?>', 
            id: <?=(int)$id?>, 
            catalog_id: '<?=$idcat?>'
        },
        
        saveAction: function(publish,preview,close) {
            this.saveParams.publish = publish;
            
            <?
        	   if (is_array($objectRenderer->fields_def)) foreach ($objectRenderer->fields_def as $name => $def) {
        	       if (!isset($def['editor_str'])) continue;
                   $save = $def['editor_str'].'_save';
        		   if (function_exists($save)) 
        		       $save($def);
        	   }
            ?>
            
            <?if ($idcat == CATALOG_VIRTUAL_USERS) {?>
            this.saveParams['groups[]'] = [0];
            if (this.mGrid.store.getCount()) {    
                this.mGrid.store.each(function(r) {
                    this.saveParams['groups[]'].push(r.get('id'));
                }, this);
            }
            <?}?>
			
			if (!this.getForm().isValid()) {
				if (Cetera.getApplication) Cetera.getApplication().msg('<span style="color:red">'+Config.Lang.materialNotSaved+'</span>', Config.Lang.materialFixFields, 3000);
				var f = this.getForm().findInvalid();
				if (f) {
					f.getAt(0).ensureVisible();
					return;
				}
			}			
			
            this.getForm().submit({
                url:'/<?=CMS_DIR?>/include/action_material_save.php', 
                params: this.saveParams,
                waitMsg:'<?=$translator->_('Подождите ...')?>',
                scope: this,
                success: function(form, action) {
					if (Cetera.getApplication) Cetera.getApplication().msg(Config.Lang.materialSaved, '', 1000);
                    this.saveParams.id = action.result.id;
					this.fireEvent('material_saved', this.saveParams);
					this.win.returnValue = {id: action.result.id, name: form.getValues().name, values: form.getValues()};
                    <?if ($idcat < 0) {?>
                        this.win.close();
                    <?} else {?>
                        this.getForm().findField('alias').setValue(action.result.alias);
                        if (preview) window.open('/<?=PREVIEW_PREFIX?>' + this.win.preview + '/' + action.result.alias);
                        if (close) this.win.close();
                    <?}?>
                },
				failure: function(form, action) {
					var s = '';
					//console.log(action);
					if (action.result)
					{
						Ext.Object.each(action.result.errors, function(key, value, myself) {
							s += value + '<br>';
						});	
						if (Cetera.getApplication) Cetera.getApplication().msg('<span style="color:red">'+Config.Lang.materialNotSaved+'</span>', s, 3000);
					}
					else
					{
						var obj = Ext.decode(action.response.responseText);
						if (Cetera.getApplication) Cetera.getApplication().msg('<span style="color:red">'+Config.Lang.materialNotSaved+'</span>', '', 3000);
						var win = Ext.create('Cetera.window.Error', {
							msg: obj.message,
							ext_msg: obj.ext_message
						});
						win.show();						
					}
			
					
					var f = form.findInvalid();
					if (f) {
						f.getAt(0).ensureVisible();
					}
	
				}
            });
        }
        
    });
    
    MaterialEditor<?=$objectDefinition->id?> = MaterialEditorBase<?=$type?>;
	MaterialEditor<?=ucfirst($objectDefinition->alias)?> = MaterialEditorBase<?=$type?>;
    
    <?
    if ($plugin) {
        if (substr($plugin, -4) != '.php') $plugin .= '.php';
        if (file_exists((PLUGIN_MATH_DIR.'/'.$plugin)))
            include(PLUGIN_MATH_DIR.'/'.$plugin);
    }
       
} catch (\Exception $e) {

    while(ob_get_level()) ob_end_clean();
    ?>
    MaterialEditor<?=$type?> = function(conf) {
        conf.win.hide();
        var win = Ext.create('Cetera.window.Error', {
            msg       : '<?=addslashes(strtr($e->getMessage(), array("\n" => "", "\r" => "")))?>',
            ext_msg   : '<?=addslashes(strtr(Util::extErrorMessage($e), array("\n" => "", "\r" => "")))?>'
        });
        win.show();
    };
	
	<? if ($objectDefinition) : ?>
	MaterialEditor<?=ucfirst($objectDefinition->alias)?> = MaterialEditor<?=$type?>;
	<? endif; ?>
	
    <? 
}
?>
