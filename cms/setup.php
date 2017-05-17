<?
if (!$application) {
   header('Location: /cms/index.php');
   die();
}

if (file_exists(WWWROOT.INSTALL_SCRIPT))
{
	unlink(WWWROOT.INSTALL_SCRIPT);
}

$translator = $application->getTranslator();
if (isset($_REQUEST['locale'])) $application->setLocale($_REQUEST['locale']);
?>
<script> 

function textPanel(text) {
    return Ext.create('Ext.Panel', {
        border: false,
        autoHeight: true,
        padding: 5,
        html: text
    });
}

var win, prev, next, retry, current_step, locale, form;

Ext.onReady(function(){

    Ext.QuickTips.init();

    prev = Ext.create('Ext.Button', {
            text: '<< <?=$translator->_('Назад')?>',
            handler: function(){
                current_step--;
                step();
            }
    });
    
    retry = Ext.create('Ext.Button', {
            text: '<?=$translator->_('Повторить')?>',
            handler: function(){
                step();
            }
    });
    
    next = Ext.create('Ext.Button', {
            text: '<?=$translator->_('Дальше')?> >>',
            handler: function(){
                current_step++;
                step();
            }
    });

    win = Ext.create('Ext.Window', {
        title: '<?=(APP_NAME.' v'.VERSION.' - Setup')?>',
        width: 450,
        autoShow: true, 
        autoHeight: true,        
        closable: false,
        resizable: false,
        bodyStyle:'background-color:#FFFFFF', 
        renderTo: Ext.getBody(),        
        buttons: [prev,retry,next]
    });
    current_step = 1;
    
    step();    
});

function step() {
    //console.log(current_step);
    eval('step'+current_step+'()');
}

function step11() {
    location.reload();
}

function step10() {
    form = false;
    buttons_ok();
    prev.hide();
    win.removeAll(true);
    win.add(textPanel('<div class="welcome"><?=sprintf($translator->_('установка системы %s прошла успешно'),APP_NAME)?></div>'));
    win.setWidth(405);
    win.doLayout();
}

// Развертывание начального сайта
var step9_values = {};
function step9() {
	if (form)
		step9_values = form.getForm().getValues();
	request_action(step9_values);
	//submit_form();
}

// Развертывание начального сайта 
function step8() {

    form = false;    
    win.removeAll(true);
	
    build_form(480, 400, [
        { xtype: 'label', html:  '<h1><?=$translator->_('Установить стандартную тему?')?></h1>' },
        {
            xtype: 'radiogroup',
            items: [
                {
                    boxLabel: '<?=$translator->_('да')?>', 
                    name: 'create', 
                    inputValue: 1,
                    checked: true
                },
                {
                    boxLabel: '<?=$translator->_('нет, нужна чистая установка')?>', 
                    name: 'create',
                    inputValue: 0
                }
            ]
        },
		{
			xtype: 'combo',
            fieldLabel: '<?=$translator->_('Выберите тему:')?>',
            valueField: 'id',
            displayField: 'title',
            name: 'theme',
            store: new Ext.data.JsonStore({
				fields: ['id', 'title'],
                root: 'rows',
                autoLoad: true,
                proxy: {
					type: 'ajax',
                    url: 'include/data_themes_avail.php'
                }
            }),
			value: 'corp',
            triggerAction: 'all',
            editable: false,
            allowBlank: true     
		}
    ]);

}

// Создание учетной записи администратора
function step7() {
    submit_form();
}

// Ввод параметров учетной записи администратора
function step6() {

    build_form(400, 245,[
        { xtype: 'label', html:  '<h1><?=$translator->_('Введите данные администратора системы')?></h1>' },
        { name: 'login', fieldLabel: '<?=$translator->_('Пользователь')?>', allowBlank: false, value: 'admin' },
        { name: 'email', fieldLabel: '<?=$translator->_('E-mail')?>' },
        { name: 'password', fieldLabel: '<?=$translator->_('Пароль')?>', inputType: 'password', allowBlank: false },
        { name: 'password2', fieldLabel: '<?=$translator->_('Повторите пароль')?>', inputType: 'password', allowBlank: false }
    ]);

}

// Структура БД и импорт данных
function step5() {
    request_action();
}

// Установка соединения с БД
function step4() {
    submit_form();
}

// Ввод реквизитов доступа к БД
function step3() {

    build_form(400, 225, [
        { xtype: 'label', html: '<h1><?=$translator->_('Настройка доступа к БД MySQL')?></h1>' },
        { name: 'dbhost', fieldLabel: 'Host', allowBlank: false, value: '<?=$application->getVar('dbhost')?>' },
        { name: 'dbname', fieldLabel: 'Database name', allowBlank: false, value: '<?=$application->getVar('dbname')?>' },
        { name: 'dbuser', fieldLabel: 'Username', allowBlank: false, value: '<?=$application->getVar('dbuser')?>' },
        { name: 'dbpass', fieldLabel: 'Password', inputType: 'password', value: '<?=$application->getVar('dbpass')?>' }
    ]);

}

// Проверка окружения
function step2() {
    if ('<?=$application->getLocale()->toString()?>' != locale.getValue()) {
        location = 'index.php?locale=' + locale.getValue();
    } else {
        request_action();
    }
}

// Приветствие и выбор языка
function step1() {
    buttons_ok();
    prev.hide();

    win.removeAll(true);
    
    locale = Ext.create('Ext.form.ComboBox', {
        fieldLabel: '<?=$translator->_('Язык')?>',
        name: 'locale',
        store: Ext.create('Ext.data.SimpleStore', {
            fields: ['abbr', 'state'],
            data : [
                <?
                $locales = \Zend_Locale::getLocaleList();
                $l = $application->getLocale();
                $i = 0;
                $selected = 0;
                foreach($locales as $locale => $exists) if ($exists) {
                    if ($translator->isAvailable($locale)) {
                        if ($i) print ','; $i=1;
                        if (!$selected) $selected = $locale;
                        if ($l->toString() == $locale) $selected = $locale;
                        print '["'.$locale.'","'.$l->getTranslation($locale, 'language', $locale).'"]';
                        print "\n";
                    }
                }
                ?>	
            ]
        }),
        valueField:'abbr',
        displayField:'state',
        queryMode: 'local',
        triggerAction: 'all',
        editable: false,
        width: 260,
        value: '<?=$selected?>'
    });
    
    var form = Ext.create('Ext.form.FormPanel', {
        items: [
            textPanel('<div class="welcome"><img src="images/brand_small.gif" width="323" height="40" /><br /><br /><?=$translator->_('Добро пожаловать в программу установки')?><br /><strong><?=APP_NAME?> <?=VERSION?></strong></div>'),
            locale
        ],
        waitMsgTarget: true,
        border: false,
        padding: 5
    });
    
    win.add(form);
    win.setWidth(405);
    win.center();
    win.doLayout();
}

function build_form(w, h, items) {
    if (form) {
        current_step--;
        step();
        return;
    }

    form = Ext.create('Ext.form.FormPanel', {
        items: items,
        waitMsgTarget: true,
        url: 'include/setup.php?locale=<?=$application->getLocale()?>',
        baseParams: {step: current_step},
        fieldDefaults : { 
            labelWidth: 105
        },
        defaults   : { anchor: '0' },
        defaultType: 'textfield',
        border: false,
        padding: 10
    });
    
    buttons_ok();
    win.setWidth(w);
    win.removeAll(true);
    win.add(form);
    win.doLayout();
    win.center();    
}

function request_action(params) {
    if (!params) params = {};
    params.step = current_step;

    form = false;

    prev.show();
    buttons_disable();
    
    win.removeAll(true);   
    win.update('<br><br>');
    win.setWidth(500);  
    win.setLoading(true);  
    //win.center();
    Ext.Ajax.request({
        url: 'include/setup.php?locale=<?=$application->getLocale()?>',
        params: params,
        scope: this,
		timeout: 600000,
        success: function(resp) {
            var obj = Ext.decode(resp.responseText);
            win.add(textPanel(obj.text));
            win.doLayout();
            win.center();
            win.setLoading(false);
            if (obj.error) {
                buttons_error();
            } else {
                buttons_ok();
            }
            if (obj.warning) {
                retry.show();
                retry.enable();
            }
        },
        failure: function() {
            buttons_error();
            win.center();
            win.setLoading(false);
        }
    });
}

function submit_form() {
    buttons_disable();
    
    if (!form) {
        current_step--;
        step();
        return;
    }
    
    form.getForm().submit({
        waitMsg:'<?=$translator->_('Подождите...')?>',
        success: function(form, action) {
            current_step++;
            step();
        },
        failure:  function(form, action) {
            buttons_error();
            var obj = Ext.decode(action.response.responseText);
            if (obj.message) alert(obj.message);
        }
    });
}

function buttons_error() {
    prev.show();
    prev.enable();
    next.hide(); 
    retry.show();
    retry.enable();
}

function buttons_ok() {
    prev.show();
    prev.enable();
    next.show(); 
    next.enable();
    retry.hide();
}

function buttons_disable() {
    prev.disable();
    next.disable();
    retry.disable();
}

</script>
