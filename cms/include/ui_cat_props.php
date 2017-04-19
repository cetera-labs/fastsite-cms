<?php
namespace Cetera;

header('Content-Type: application/javascript; charset=UTF-8');

/**
 * Cetera CMS 3
 * 
 * Свойства раздела   
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru) 
 * @author Roman Romanov <nicodim@mail.ru> 
 **/

try {  

    ob_start();
      
    include('common_bo.php');
       
    $lang_res_perm = array(
        PERM_CAT_OWN_MAT => $translator->_('Работа со своими материалами'),
        PERM_CAT_ALL_MAT => $translator->_('Работа с материалами других авторов'),
        PERM_CAT_ADMIN   => $translator->_('Администрирование раздела'),
        PERM_CAT_VIEW	   => $translator->_('Возможность видеть раздел в структуре'),
        PERM_CAT_MAT_PUB => $translator->_('Публикация материалов')
    );
    
    $id = (int)$_GET['id'];
    if (!$user->allowCat(PERM_CAT_ADMIN,$id) || ($id == 0 && !$user->allowAdmin())) { 
        ?>CatProps = function(config) {
            config.win.hide();
            var win = new ErrorWindow({
                msg       : '<?php echo $translator->_('Недостаточно полномочий для совершения этого действия'); ?>'
            });
            win.show(); 
            this.show = function() {};  
        }<?
        die();
    }
    
    $wheight = 350;
    $title  = '';
    
    $catalog = Catalog::getById((int)$id);
    
    $permissions = !$catalog->isLink() && $user->allowAdmin();
    $variables = $catalog->isRoot() || $catalog->isServer();
	$robots = $catalog->isServer() && $user->allowAdmin();
           
    $title = $translator->_('Свойства раздела').' "'.addslashes($catalog->name).'" (ID='.$catalog->id.')';
    
    $cat_type = 'group'; // link server folder
    
    if ($catalog->isLink()) {
        $wheight = 290;
    } elseif ($catalog->isServer()) {
        $wheight = 400;
    }
    
    $math = Catalog::TABLE;
    if (!$catalog->isLink() && !$catalog->isRoot()) {
        if ($application->getVar('editor.autoflow'))
            $page_height = $wheight;
            else $page_height = -1;    
        $objectDefinition = new ObjectDefinition(Catalog::TYPE);
        $objectRenderer = new ObjectRenderer($objectDefinition, false, $catalog->id, $page_height, $translator->_('Свойства'));   
        $objectRenderer->setObject($catalog);  
        //unset($fields_def['name']);
       // unset($fields_def['alias']);         
    } else {
        $objectRenderer = false;
    }
    ?>
    
    Ext.define('CatProps', {
		
		requires: 'Cetera.field.MaterialType',
    
        extend:'Ext.FormPanel',
        border: false,
        bodyBorder: false,
        bodyStyle:'background: none',
        margin: '2 0 0 0',
        waitMsgTarget: true,
        monitorValid: true,
        fieldDefaults: {
            labelAlign: 'right',
            labelWidth: 110
        },
                               
        initComponent : function() {    
        
        <?php if ($objectRenderer) $objectRenderer->initalizeFields(); ?> 
        
        <?if ($variables) {?>
        
            this.varsStore = new Ext.data.JsonStore({
                proxy: {
                    type: 'ajax',
                    url: 'include/data_vars.php',
                    reader: {
                        type: 'json',
                        root: 'rows',
                        idProperty: 'name'
                    },
                    extraParams: {id: <?=$id?>}
                },
                fields: ['id','name','value','value_orig','describ']
            });
            
            this.varDescrib = new Ext.Panel({
                bodyStyle: 'padding: 2px',
                height: 50
            });
            
            this.varsGrid = new Ext.grid.GridPanel({
                height: 265,
                margin: '0 0 5 0',
                store: this.varsStore,
                loadMask: true,
                plugins: [Ext.create('Ext.grid.plugin.RowEditing', {
                    clicksToMoveEditor: 1,
                    autoCancel: true
                })],
                columns: [
            		    {
                        header: '<?=$translator->_('Имя')?>', 
                        dataIndex: 'name', 
                        sortable: true,
                        width: 200
                    },{
                        header: '<?=$translator->_('Значение')?>', 
                        dataIndex: 'value',
                        sortable: true,
                        renderer: this.varValue,
                        flex: 1,
                        editor: {}
                    }
            	],
            	tbar: [
                    {
                        iconCls:'icon-reload',
                        tooltip:'<?=$translator->_('Обновить')?>',
                        handler: function () { this.varsStore.reload(); },
                        scope: this
                    },'-'<?if ($catalog->isRoot()) {?>,{
                        iconCls:'icon-new',
                        tooltip:'<?=$translator->_('Новая переменная')?>',
                        handler: function () {
                            var win = this.getVarEditWindow();
                            this.varNameField.setValue(''); 
                            this.varValueField.setValue(''); 
                            this.varDescribField.setValue(''); 
                            win.show();
                        },
                        scope: this
                    },{
                        iconCls:'icon-edit',
                        tooltip:'<?=$translator->_('Редактировать')?>',
                        handler: function () {
                            if (this.selectedVar === false) return;
                            var win = this.getVarEditWindow();
                            var data = this.varsStore.getAt(this.selectedVar).data;
                            this.varNameField.setValue(data.name); 
                            this.varValueField.setValue(data.value); 
                            this.varDescribField.setValue(data.describ); 
                            win.show();
                        },
                        scope: this
                    },{
                        iconCls:'icon-delete',
                        tooltip:'<?=$translator->_('Удалить')?>',
                        handler: function () {
                            if (this.selectedVar !== false)
                                this.varsStore.removeAt(this.selectedVar);
                            this.selectedVar = false;
                        },
                        scope: this
                    }<?} else {?>,{
                        iconCls:'icon-edit',
                        tooltip:'<?=$translator->_('Переопределить')?>',
                        handler: function () {
                            if (this.selectedVar === false) return;
                            var win = this.getVarEditWindow();
                            var data = this.varsStore.getAt(this.selectedVar).data;
                            this.varNameField.setValue(data.name); 
                            this.varValueField.setValue(data.value); 
                            this.varDescribField.setValue(data.describ); 
                            win.show();
                        },
                        scope: this
                    },{
                        iconCls:'icon-delete',
                        tooltip:'<?=$translator->_('Удалить переопределение')?>',
                        handler: function () {
                            if (this.selectedVar === false) return;
                            var rec = this.varsStore.getAt(this.selectedVar);
                            rec.set('value',rec.get('value_orig'));
                        },
                        scope: this
                    }
                    <?}?>
                ],
                selModel: new Ext.selection.RowModel({
                    listeners: {
                        'select': {
                            fn: function(sm, r, rowIdx) {
                                this.selectedVar = rowIdx;
                                this.varDescrib.update(r.get('describ'));
                            },
                            scope: this
                        },
                        'deselect': {
                            fn: function(sm, r, rowIdx) {
                                this.selectedVar = false;
                                this.varDescrib.update('');
                            },
                            scope: this
                        }
                    }
                }),
                listeners: {
                    viewready: {
                        fn: function(grid) {
                            grid.store.load();
                        }
                    }
                }
            });
            
            this.selectedVar = false;
            this.varEditWindow = null;
            
            this.getVarEditWindow = function() {
                if (!this.varEditWindow) {
                
                    this.varNameField = new Ext.form.TextField({fieldLabel: '<?=$translator->_('Имя')?>'});
                    this.varValueField = new Ext.form.TextField({fieldLabel: '<?=$translator->_('Значение')?>'});
                    this.varDescribField = new Ext.form.TextField({fieldLabel: '<?=$translator->_('Описание')?>'});
                
                    <?if (!$catalog->isRoot()) {?>
                        this.varNameField.disabled = true;
                        this.varDescribField.disabled = true;
                    <?}?>
                
                    this.varEditWindow = new Ext.Window({
                        modal: true,
                        closeAction: 'hide',
                        width: 400,
                        height: 150,
                        layout: 'anchor',
                        bodyStyle: 'padding:5px',
                        items: [this.varNameField, this.varValueField, this.varDescribField],
                        defaults: { anchor: '0' },
                        buttons: [{
                            text:'<?=$translator->_('ОК')?>',
                            handler: function() { 
                                if (this.selectedVar !== false) {
                                    var rec = this.varsStore.getAt(this.selectedVar);
                                    rec.set('name',this.varNameField.getValue());
                                    rec.set('value',this.varValueField.getValue());
                                    rec.set('describ',this.varDescribField.getValue());
                                } else {
                                    var n = this.varsStore.getCount();
                                    var p = {
                                        id: 0,
                                        name: this.varNameField.getValue(),
                                        value: this.varValueField.getValue(),
                                        describ: this.varDescribField.getValue()
                                    };
                                    this.varsStore.insert(n, p);
                                }
                                this.varEditWindow.hide(); 
                            },
                            scope: this
                        },{
                            text:'<?=$translator->_('Отмена')?>',
                            handler: function() { this.varEditWindow.hide(); },
                            scope: this
                        }]
                    });
                }
                return this.varEditWindow;
                
            }
            
            this.varValue = function(val,m,rec) {
                if(val != rec.get('value_orig')) {
                    return '<b>' + val + '</b>';
                }
                return val;
            }
        
        <?}?>

    
        <?if ($permissions) {?>
           
            this.selectedGroup = 0;
            
            this.hiddenInherit = new Ext.form.Hidden({
                name:  'cat_inherit',
                value: <?=($catalog->isInheritsPermissions()?Catalog::INHERIT:0)?>
            });
            
            // список разрешений
            this.permGrid = new Ext.grid.GridPanel({
                margin: '5 0 0 0',
                enableHdMenu     : false,
                enableColumnMove : false,
                enableColumnResize: false,
                store: new Ext.data.SimpleStore({
                    fields: ['id', 'name', 'checked'], data: []
                }),
                columns: [
              		{
                      header: '<?=$translator->_('Разрешения')?>', 
                      flex: 1,
                      dataIndex: 'name'
                  },{
                      xtype: 'checkcolumn',
                      header: '<?=$translator->_('Разрешить')?>',
                      dataIndex: 'checked',
                      width: 65,
                      listeners: {
                          checkchange: function (c, rowIndex, checked, eOpts) {
                              var record = this.permGrid.store.getAt( rowIndex );
                              var groups = this.permissions[record.getId()].groups;
                              if (checked) {
                                  groups.push(this.selectedGroup)
                              } else {
                                  groups.remove(this.selectedGroup);
                              }
                          }, 
                          scope: this
                      }
                  }
              	],
                selModel: new Ext.selection.RowModel({
                    listeners: { 'beforeselect' : function() { return false; } }
                }),
                height           : 145
            });
            
            // список групп
            this.groupsGrid = new Ext.grid.GridPanel({
                loadMask: true,
                store : new Ext.data.JsonStore({
                    root: 'rows',
                    fields: ['id', 'name'],
                  
                    proxy: {
                        type: 'ajax',
                        url: 'include/data_groups.php'
                    },
              
                    listeners: {
                        'load': {
                            fn: function() {
                                this.groupsGrid.getSelectionModel().select(0);
                            },
                            scope: this
                        }
                    }
                }),
                selModel: new Ext.selection.RowModel({
                    listeners: {
                        'select' : {
                            fn: function(sm,r,i) {
                                this.selectedGroup = parseInt(r.data['id']);
                                var a = [];
                                Ext.each(this.permissions, function(item, index) {
                                    if (item) a.push([index, item.name, this.selectedGroup==<?=GROUP_ADMIN?> || item.groups.indexOf(this.selectedGroup)>=0]);
                                }, this);
                                this.permGrid.getStore().loadData(a);
                                this.checkPermGrid();
                            },
                            scope: this
                        },
                        'deselect' : {
                            fn: function() {
                                this.checkPermGrid();
                            },
                            scope: this
                        }
                    }
                }),
                columns: [
            		{width: 20, renderer: function(v, m) { m.css = 'icon-users'; } },
            		{dataIndex: 'name', flex: 1}
            	],
                listeners: {
                    viewready: {
                        fn: function(grid) {
                            grid.store.load();
                        }
                    }
                },
                hideHeaders      : true,
                height           : 120
            });
            
            // матрица разрешений для раздела
            this.permissions = [];
            <?
            $cat = $catalog;
            while ($cat->isInheritsPermissions()) $cat = $cat->parent;        	
            
            foreach($lang_res_perm as $pid => $value) {
                $gr = array();
                $r = fssql_query('SELECT group_id FROM users_groups_allow_cat WHERE permission='.$pid.' and catalog_id='.$cat->id);
                while ($perm = mysql_fetch_assoc($r)) $gr[] = $perm['group_id'];
                print "this.permissions[$pid] = {name:'$value', groups:[".implode(',',$gr)."]};\n";
            }
            ?>
            
            this.checkPermGrid = function() {
                this.permGrid.setDisabled(this.groupsGrid.getSelectionModel().getCount()==0 || this.hiddenInherit.value>0 || this.selectedGroup==<?=GROUP_ADMIN?>);
            }
        
        <?}?>
        
            this.controllerLookupStore = Ext.create('Ext.data.JsonStore', {
                fields: ['name'],
                root: 'rows',
                proxy: {
                    type: 'ajax',
                    url: 'include/data_templates.php'
                },
                extraParams: {
                    'catalog_id': <?=$catalog->id?>
                }                                    
            });        
    
            this.items = [{
                xtype:'tabpanel',
                plain:true,
                activeTab: 0,
                height: <?=$wheight?>,
                bodyStyle:'background: none;',
                border    : false,
                defaults:{bodyStyle:'background:none; padding:5px'},
                items:[
                    <?if ($id) {?>
                    { // BEGIN common tab
                        title      : '<?=$translator->_('Основные')?>',
                        layout     : 'anchor',
                        border     : false,
                        bodyBorder : false,
                        defaults   : { anchor: '0', hideEmptyLabel: false },
                        defaultType: 'textfield',
                        items: [
                            {
                                fieldLabel: '<?=$translator->_('Имя')?>',
                                name: 'name',
                                allowBlank:false,
                                value: '<?=addslashes($catalog->name)?>'
                            }, 
                            {
                                fieldLabel: '<?=($catalog->isServer()?$translator->_('Домен'):$translator->_('Alias'))?>',
                                name: 'alias',
                                allowBlank:false,
                                regex: /^[\.\-\_A-Z0-9А-Я]+$/i,
                                value: '<?=$catalog->alias?>'
                            }
                            
                            <?if ($catalog->isServer()) {?>
                            ,
                            Ext.create('Cetera.field.Aliases',{
                                fieldLabel: '<?=$translator->_('Синонимы')?>',
                                name: 'server_aliases',
                                store: new Ext.data.SimpleStore({
                                    fields: ['name'],
                                    data: [
                                        <?
                                          $r1 = fssql_query("select name from server_aliases where name<>'".$catalog->alias."' and id=".$catalog->id." order by name");
                                          $first = 1;
                                          while ($f1 = mysql_fetch_row($r1)) {
                                            if (!$first) print ',';
                                            print "['$f1[0]']\n";
                                            $first = 0;
                                          }
                                        ?>
                                    ]
                                })
                            })
                            <?}?>
                            
                <?if ($catalog->isServer()) {?>
                            ,{
                                fieldLabel: '<?=$translator->_('Каталог')?>',
                                name: 'templateDir',
                                regex: /^[\/\.\-\_A-Z0-9]+$/i,
                                value: '<?=$catalog->templateDir?>',
                                listeners: {
                                    blur: {
                                        scope: this,
                                        fn: function(fld){ 
                                            this.controllerLookupStore.proxy.extraParams.templateDir = fld.getValue();
                                        }
                                    }
                                }                                
                            }
                <?}?>  
                                       
                            ,{
                                xtype: 'combo',
                                fieldLabel: '<?=$translator->_('Контроллер')?>',
                                valueField: 'name',
                                displayField: 'name',
                                name: 'template',
                                store: this.controllerLookupStore,
                                triggerAction: 'all',
                                selectOnFocus:true,
                                value: '<?=$catalog->template?>'         
                            }
                
                <?if (!$catalog->isServer()) {?>
                            ,Ext.create('Cetera.field.Folder', {
                                name: 'parentid',
                                allowBlank: true,
                                fieldLabel: '<?=$translator->_('Размещение')?>',
                                displayValue: '<?=addslashes($catalog->parent->getPath()->implode())?>',
                                path: '<?=$catalog->parent->getTreePath()?>',
                                value: <?=$catalog->parent->id?>,
                                exclude: <?=$catalog->id?>,
                                nolink: 1,
                                rule: <?=PERM_CAT_ADMIN?>
                            })
                <?}?>  
                
                <?if (!$catalog->isLink()) {?>
                            ,{
                                xtype: 'materialtypefield',
                                fieldLabel: '<?=$translator->_('Тип материалов')?>',
                                name: 'typ',
                                empty: 1,
								linkable: 1,
                                allowBlank: false,
                                <?if ($catalog->materialsCount) {?>disabled:true,<?}?>
                                value: <?=(int)$catalog->materialsType?>       
                            },
                            {
                                xtype: 'checkbox',
                                boxLabel: '<?=$translator->_('автоматически заполнять alias материалов')?>',
                                name: 'autoalias',
                                labelSeparator: '',
                                checked: <?=(int)($catalog->catalogType&Catalog::AUTOALIAS)?>,
                  	            listeners: {
                  	                check: {
                                        fn: function(el, checked) {
                      	                    this.ownerCt.getComponent("auto1").setDisabled(!checked);
                      	                    this.ownerCt.getComponent("auto2").setDisabled(!checked);
                                        }
                  	                }
                  	            },
                	              inputValue: 1
                            },
                            {
                                xtype: 'radio',
                                itemId: 'auto1',
                                boxLabel: '<?=$translator->_('на основании даты')?>',
                                name: 'autoalias_type',
                                labelSeparator: '',
                                disabled: <?=(int)!($catalog->catalogType&Catalog::AUTOALIAS)?>,
                                checked: <?=(int)!($catalog->catalogType&Catalog::AUTOALIAS_TRANSLIT)?>,
                	              inputValue: 1
                            },
                            {
                                xtype: 'radio',
                                itemId: 'auto2',
                                boxLabel: '<?=$translator->_('транслит заголовка')?>',
                                name: 'autoalias_type',
                                labelSeparator: '',
                                disabled: <?=(int)!($catalog->catalogType&Catalog::AUTOALIAS)?>,
                                checked: <?=(int)($catalog->catalogType&Catalog::AUTOALIAS_TRANSLIT)?>,
                	              inputValue: 2
                            },
                            {
                                xtype: 'checkbox',
                                boxLabel: '<?=$translator->_('скрытый')?>',
                                name: 'hidden',
                                labelSeparator: '',
                                checked: <?=(int)$catalog->hidden?>,
                	            inputValue: 1
                            }
                <?
                }
                if ($catalog->isLink()) {
                ?>
                            ,Ext.create('Cetera.field.Folder', {
                                name: 'typ',
                                fieldLabel: '<?=$translator->_('Связан с')?>',
                                displayValue: '<?=addslashes($catalog->prototype->getPath()->implode())?>',
                                path: '<?=$catalog->prototype->getTreePath()?>',
                                value: <?=$catalog->prototype->id?>,
                                exclude: <?=$catalog->id?>,
                                nolink: 1,
                                allowBlank: false,
                                rule: 7
                            })
                <?}?>
                        ]
                    } // END common tab
                    
                    <?}?>
					
					<? if ($robots) : ?>
                    ,{ 
                        title  : 'robots.txt',
                        border    : false,
                        bodyBorder: false,
                        layout     : 'anchor',
                        defaults   : { anchor: '100% 100%' },						
                        items  : [
                            {
                                xtype: 'textarea',
                                name: '_robots_txt',
								value: '<?=str_replace("\r",'\r',str_replace("\n",'\n',addslashes($catalog->getRobots())))?>'
                            },
                        ]
                    }				
					<? endif; ?>
                    
                    <?if ($permissions) {?>
    
                    <?if ($id) {?>,<?}?>
                    { // BEGIN permissions
                        title  : '<?=$translator->_('Разрешения')?>',
                        border    : false,
                        bodyBorder: false,
                        items  : [
                            <?if ($id) {?>
                            {
                                xtype: 'checkbox',
                                boxLabel: '<?=$translator->_('наследовать разрешения родительского раздела')?>',
                                submitValue: false,
                                labelSeparator: '',
                                checked: <?=($catalog->isInheritsPermissions()?'true':'false')?>,
                	              listeners: {
                                    'check': {
                                        fn: function(el) { 
                                            this.hiddenInherit.setValue(el.checked?<?=Catalog::INHERIT?>:0);
                                            this.checkPermGrid();
                                        },
                                        scope: this
                                    }
                                }
                            },
                            <?}?>
                            this.hiddenInherit, 
                            {
                                xtype : 'label',
                                html  : '<?=$translator->_('Группы')?>:',
                                style: 'padding: 3px;'
                            }, 
                            this.groupsGrid, 
                            this.permGrid
                        ]
                    } // END permissions
    
                    <?}?>
                    
                    <?php if ($variables) : ?>
                    ,{ // BEGIN vars
                        title  : '<?=$translator->_('Переменные')?>',
                        border    : false,
                        bodyBorder: false,
                        items  : [
                            this.varsGrid,
                            this.varDescrib
                        ]
                    } // END vars
                    <?php endif; ?>
                    
                    <?php if ($objectRenderer && count($objectRenderer->fields_def)) : ?>
                    // BEGIN User defined fields
                    ,<?php $objectRenderer->renderFields(); ?>
                    // END User defined fields
                    <?php endif; ?>
                    
                ]
            }];

            
            this.callParent();
        },
        
        save: function() {
        
            <?
        	  if (is_array($objectRenderer->fields_def)) foreach ($objectRenderer->fields_def as $name => $def) {
        	       if (!isset($def['editor_str'])) continue;
                   $save = $def['editor_str'].'_save';
        		   if (function_exists($save)) 
        		       $save($def);
        	  }
            ?>
                    
            var form = this.getForm();
            var me   =  this;
            var tree = Ext.getCmp('main_tree');              
            
            var params = {
                action: 'cat_save', 
                id: <?=$id?>
            };
    
            <?if ($permissions) {?>
            if (!me.hiddenInherit.value) Ext.each(me.permissions, function(item, index) {
                if (item) {
                  params['permissions['+index+'][]'] = item.groups;
                }
            }, me);
            <?}?>
            
            <?if ($variables) {?>
            var i = 0;
            me.varsStore.each(function(rec) {
                params['vars['+i+'][id]'] = rec.get('id');
                params['vars['+i+'][name]'] = rec.get('name');
                params['vars['+i+'][value]'] = rec.get('value');
                params['vars['+i+'][describ]'] = rec.get('describ');
                i++;
            },me);
            <?}?>
            
            form.submit({
                url:'include/action_catalog.php', 
                params: params,
                waitMsg:'<?=$translator->_('Подождите ...')?>',
                scope: this,
                success: function(form, action) {
                    tree.reload(action.result.path);                  
                    this.wnd.hide();
                }
            });
                            
        }        
        
    });    
   
    Ext.define('CatPropsWindow', {
        extend:'Ext.Window',
        closable:true,
        width:730,
        height:<?=($wheight+75)?>,
        title: '<?=$title?>',
        plain:true,
        layout: 'fit',
        resizable: true,
        modal: true,
        
<?php if ($user->allowAdmin() && !$catalog->isRoot()) : ?>
        tools:[{
                type:'gear',
                tooltip: '<?=$translator->_('Настроить')?>',
                handler: function(){
                    if (!this.prefsWindow) this.prefsWindow = Ext.create('CatPrefsWindow');
                    this.prefsWindow.show();
                },
        }],
<?php endif; ?>                
        
        initComponent : function() {
        
            this.prefsWindow = false;
        
            this.formPanel = Ext.create('CatProps', {
                wnd: this
            });
            
            this.items = [this.formPanel];
            
            this.okButton = Ext.create('Ext.Button',{
                text: '<?=$translator->_('OK')?>', 
                scope: this.formPanel,
                handler: this.formPanel.save
            }); 
            
            this.cancelButton = Ext.create('Ext.Button',{
                text: '<?=$translator->_('Отмена')?>',
                scope: this,
                handler: function() { this.close(); }
            });             
            
            this.buttons = [this.okButton, this.cancelButton]; 
            
            this.on('close', function(){
                if (this.prefsWindow) this.prefsWindow.destroy();
            }, this);
            
            this.callParent();
        }                   
    });  
    
<?php if ($user->allowAdmin()) : ?>

    Ext.define('CatPrefsWindow', {
        extend:'Ext.Window',
        closable:true,
        width:500,
        height: 400,
        title: '<?=$translator->_('Настройки раздела')?>',
        plain:true,
        layout: 'fit',
        resizable: true,
        closeAction: 'hide',
        modal: true,
        
        initComponent : function() {
                    
            this.okButton = Ext.create('Ext.Button',{
                text: '<?=$translator->_('OK')?>', 
                scope: this,
                handler: this.save
            }); 
            
            this.cancelButton = Ext.create('Ext.Button',{
                text: '<?=$translator->_('Отмена')?>',
                scope: this,
                handler: function() { this.hide(); }
            });             
            
            this.buttons = [this.okButton, this.cancelButton]; 
            
            // список полей материалов
            this.fieldsGrid = new Ext.grid.GridPanel({
                enableHdMenu     : false,
                enableColumnMove : false,
                enableColumnResize: false,
                region: 'center',
                disabled: <?=($catalog->inheritFields?'true':'false')?>,
                store: new Ext.data.SimpleStore({
                    fields: ['id', 'name', 'force_show', 'force_hide', 'shw'], data: [
<?php
if ($catalog->materialsType) {

    $fields = $catalog->materialsObjectDefinition->getFields();
    $_first = 1;
    
	$fields_over = array();	
    $r = fssql_query('SELECT field_id, force_show, force_hide FROM types_fields_catalogs WHERE catalog_id='.$catalog->id.' and type_id='.$catalog->materialsType);
    while($f = mysql_fetch_assoc($r)) {
        $fields_over[$f['field_id']] = $f;
    }
    
    foreach ($fields as $f) {
        if ($f['fixed']) continue;
        if (!$_first) print ',';
        
        if (isset($fields_over[$f['field_id']])) {
			$f['force_show'] =  $fields_over[$f['field_id']]['force_show'];
			$f['force_hide'] =  $fields_over[$f['field_id']]['force_hide'];
            //$f = array_merge((array)$f, $fields_over[$f['field_id']]);
		}
        
        print '['.$f['field_id'].',\'<b>'.addslashes($f['describ'].'</b> ('.$f['name'].')').'\','.(boolean)$f['force_show'].','.(boolean)$f['force_hide'].','.(boolean)$f['shw'].']'."\n";
        $_first = 0;
    }  

}                 
?>
                    ]
                }),
                columns: [
              		{
                      header: '<?=$translator->_('Поле')?>', 
                      flex: 1,
                      dataIndex: 'name'
                  },{
                      xtype: 'checkcolumn',
                      header: '<?=$translator->_('Показать')?>',
                      dataIndex: 'force_show',
                      width: 65,
                      renderer: function (value, metaData, record) {
                          if (record.get('shw')) return;
                          return (new Ext.grid.column.CheckColumn()).renderer(value);
                      }
                  },{
                      xtype: 'checkcolumn',
                      header: '<?=$translator->_('Скрыть')?>',
                      dataIndex: 'force_hide',
                      width: 65,
                      renderer: function (value, metaData, record) {
                          if (!record.get('shw')) return;
                          return (new Ext.grid.column.CheckColumn()).renderer(value);
                      }
                  }
              	],
                selModel: new Ext.selection.RowModel({
                    listeners: { 'beforeselect' : function() { return false; } }
                }),
                height           : 145
            });            
            
            this.items = [{
                xtype:'tabpanel',
                plain:true,
                bodyStyle:'background: none;',
                border    : false,
                defaults:{bodyStyle:'background:none; padding:5px'},
                items:[
                    { 
                        title      : '<?=$translator->_('Видимость полей')?>',
                        border     : false,
                        bodyBorder : false,
                        layout: 'border',
                        items: [
                            {
                                xtype: 'checkbox',
                                height: 30,
                                region: 'north',
                                boxLabel: '<?=$translator->_('наследовать настройки родительского раздела')?>',
                                submitValue: false,
                                labelSeparator: '',
                                checked: <?=($catalog->inheritFields?'true':'false')?>,
                	              listeners: {
                                    'change': {
                                        fn: function(el) { 
                                            this.fieldsGrid.setDisabled(el.checked);
                                        },
                                        scope: this
                                    }
                                }
                            },                        
                            this.fieldsGrid
                        ]
                    }
                ]
            }];        
            
            this.callParent();
        },
         
        save: function() {
        
            var params = {
                action: 'cat_prefs',
                id: <?=$catalog->id?>,
                inheritFields: this.fieldsGrid.isDisabled()?1:0,
            };
            
            params['fields'] = {}
            this.fieldsGrid.store.each(function(rec) {
                params['fields['+rec.get('id')+'][force_show]'] = rec.get('force_show')?1:0;
                params['fields['+rec.get('id')+'][force_hide]'] = rec.get('force_hide')?1:0;
            }, this);            
            
            this.setLoading(true);
        
            Ext.Ajax.request({
                url: '/<?=CMS_DIR?>/include/action_catalog.php',
                params: params,
                scope: this,
                success: function(resp) {
                    this.setLoading(false);
                    this.hide();
                }
            });
            
        }    
                       
    });
<?php endif; ?>          

    <?php
    ob_end_flush(); 
    
} catch (Exception $e) {

    ob_end_clean();
    ?>
    CatProps = function(config) {
        var win = Ext.create('Cetera.window.Error', {
            msg       : '<?=addslashes(strtr($e->getMessage(), array("\n" => "", "\r" => "")))?>',
            ext_msg   : '<?=addslashes(strtr(Util::extErrorMessage($e), array("\n" => "", "\r" => "")))?>'
        });
        win.show();
        this.show = function() {}; 
    };
    <? 
}
?>
