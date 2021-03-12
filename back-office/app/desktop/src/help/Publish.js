Ext.define('Cetera.help.Publish', {

    extend:'Ext.Window',    
      
	cls: 'help',
	width: 300,
	height: 200,
	autoShow: true,	
	animateTarget: 'help-publish-btn',
	layout: 'card',
    bodyStyle: 'padding:15px',
    defaults: {
        // applied to each contained panel
        border: false,
		bodyStyle: 'background: none',
    },	
	
	materialsPanel : false,
	materialEditor : false,
		     
    initComponent : function() {
		
		this.card1 = Ext.create('Ext.Panel', {
			
			border: false,
			html: '<h4>Шаг 1</h4>Выберите раздел для публикации',
            listeners: {
                 scope: this,
                 'activate': function() {
                      mainTree.addCls('highlight');
					  mainTree.getSelectionModel().addListener('selectionchange', this.navigateNext, this);
					  
					  this.btnPrev.disable();
					  this.btnNext.setDisabled( !mainTree.getSelectedId() );
                 },
				 'deactivate': function() {
                      mainTree.removeCls('highlight');
					  mainTree.getSelectionModel().removeListener('selectionchange', this.navigateNext, this);
                 } 
            }			
			
		});
		
		this.card2 = Ext.create('Ext.Panel', {
			
			border: false,
			html: '<h4>Шаг 2</h4>Перейдите в режим редактирования материалов',
            listeners: {
                 scope: this,
                 'activate': function() {
					  var rec = navigation.store.getNodeById( 'materials' );
					  var nodeId = navigation.getView().getRowId(rec);
					  Ext.get( nodeId ).addCls('highlight');	

					  tabs.addListener('tabchange', this.tabChanged, this);
					  
					  this.btnPrev.enable();
					  this.btnNext.setDisabled( tabs.getActiveTab().getId()!='tab-materials' );
					  if (tabs.getActiveTab().getId()=='tab-materials') this.materialsPanel = tabs.getActiveTab().items[0];
                 },
				 'deactivate': function() {
					  var rec = navigation.store.getNodeById( 'materials' );
					  var nodeId = navigation.getView().getRowId(rec);
					  Ext.get( nodeId ).removeCls('highlight');	
					  
					  tabs.removeListener('tabchange', this.tabChanged, this);
                 } 
            }			
			
		});	
		
		this.card3 = Ext.create('Ext.Panel', {
			
			border: false,
			html: '<h4>Шаг 3</h4>Нажмите кнопку "Создать" на панели инструментов',
            listeners: {
                scope: this,
                'activate': function() {
					var btn = Ext.getCmp('tb_mat_new');
					btn.addCls('highlight');
					btn.addListener('click', this.navigateNext, this);
					this.btnNext.disable();
                },
				'deactivate': function() {
					var btn = Ext.getCmp('tb_mat_new');
					if (btn) {
						btn.removeCls('highlight');
						btn.removeListener('click', this.navigateNext, this);
					}
                } 
            }			
			
		});		

		this.card4 = Ext.create('Ext.Panel', {
			
			border: false,
			html: '<h4>Шаг 4</h4>Введите заголовок материала',
            listeners: {
                scope: this,
                'activate': function() {
					this.btnNext.disable();
					this.btnPrev.disable();
					this.materialsPanel.on('material_editor_ready',function(win, editorForm){
						this.materialEditor = editorForm;
						var btn = Ext.getCmp('editor-name');
						btn.addCls('highlight');
						btn.addListener('change', this.navigateNext, this);
					},this);
                },
				'deactivate': function() {
					var btn = Ext.getCmp('editor-name');
					if (btn) {
						btn.removeCls('highlight');
						btn.removeListener('change', this.navigateNext, this);
					}
                } 
            }			
			
		});

		this.card5 = Ext.create('Ext.Panel', {
			
			border: false,
			html: '<h4>Шаг 5</h4>Заполните другие поля в материале, затем нажмите кнопку "Сохранить и опубликовать"',
            listeners: {
                scope: this,
                'activate': function() {
					this.materialEditor.publishbut.addCls('highlight');
					this.materialEditor.addListener('material_saved', this.materialSaved, this);
                },
				'deactivate': function() {
					this.materialEditor.publishbut.removeCls('highlight');	
					this.materialEditor.removeListener('material_saved', this.materialSaved, this);					
                } 
            }			
			
		});			

		this.cardLast = Ext.create('Ext.Panel', {
			
			border: false,
			html: '<h4>Поздравляем!</h4>Вы успешно опубликовали материал',
            listeners: {
                 scope: this,
                 'activate': function() {
                      this.btnNext.disable();
					  this.btnPrev.disable();
                 }
            }			
			
		});			
    
		this.btnPrev = Ext.create('Ext.Button',{
            text: '<< ' + Config.Lang.prev,
			scope: this,
			disabled: true,
            handler: function(btn) {
				this.navigate("prev");
            }			
		});
		
		this.btnNext = Ext.create('Ext.Button',{
            text: Config.Lang.next + ' >>',
			scope: this,
			disabled: true,
            handler: function(btn) {
				this.navigate("next");
            }			
		});
	
        this.title = 'Help';  

		var app = Cetera.getApplication();
		
        Ext.apply(this, {
			title: 'Как опубликовать материал',
			x: app.viewport.width - this.width - 10,
			y: app.viewport.height - this.height - 10,
			items: [
				this.card1,
				this.card2,
				this.card3,
				this.card4,
				this.card5,
				this.cardLast
			],			
			bbar: [
				'->',
				this.btnPrev,
				this.btnNext
			]			
        });
		
        this.callParent();
		
    },
	
	close: function() {
		
		this.getLayout().getActiveItem().fireEvent('deactivate');
		this.callParent();
		
	},
	
	navigate: function(direction) {
		var layout = this.getLayout();
		layout[direction]();
	},

	navigateNext: function() {
		this.navigate("next");
	},
	
	materialSaved: function(params) {
		if (params.publish) this.navigate("next");
	},	
	
	tabChanged: function(tabs, newtab) {
		if ( newtab.getId() == 'tab-materials' ) {
			if (newtab.items.getAt(0)) {
				this.navigate("next");
				this.materialsPanel = newtab.items.getAt(0);
			} else {
				newtab.on('add', function(t, comp){
					this.navigate("next");
					this.materialsPanel = comp;					
				}, this);
			}
		}
		else this.btnNext.disable();
	},
	
});