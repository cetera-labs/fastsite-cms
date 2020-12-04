<?php
include_once('include/common.php');

$application->connectDb();
$application->initSession();
$application->initBo();

$translator = $application->getTranslator();  
include_once('include/field_editors.php');

$application->initPlugins();

header('Content-type: application/x-javascript; charset=UTF8'); 

$user = $application->getUser();
if (!$user || !$user->allowBackOffice()) { 

    $user = 'false';
    
} else {

    $user = json_encode($user->boArray());
    
}
?>
var winH = 500;
if (document.body && document.body.offseHeight) winH = document.body.offsetHeight - 100;
if (document.documentElement &&
    document.documentElement.offsetHeight ) winH = document.documentElement.offsetHeight;
if (window.innerHeight) winH = window.innerHeight;
    
Config = {

        maxWindowHeight : (winH > <?=(PAGE_HEIGHT+120)?>)?<?=(PAGE_HEIGHT+120)?>:winH,
        maxWindowWidth  : <?=(FIELD_WIDTH+LABEL_WIDTH+31)?>,
        
        cmsPath : '/<?=CMS_DIR?>/',
        libraryPath : '<?=LIBRARY_PATH?>',
        serverName: '<?=$_SERVER['SERVER_NAME']?>',
        
        defaultPageSize: 50,
        
        appName: '<?=APP_NAME?>',
        appVersion: '<?=VERSION?>',
		
		foEditMode: <?php echo isset($application->getSession()->foEditMode)?$application->getSession()->foEditMode:'false' ?>,
        
        user: <?=$user?>,
		
		groupAdmin: <?=GROUP_ADMIN?>,
		
		developerKey: <?=$application->getVar('developer_key')?'true':'false'?>,
        
        modules: null,
        
        locale: '<?php echo $application->getLocale(); ?>',
		
        contentExists: <?php echo $application->contentExists()?1:0; ?>,
		
        fields: {
            FIELD_TEXT: <?php echo FIELD_TEXT; ?>,
            FIELD_LONGTEXT: <?php echo FIELD_LONGTEXT; ?>,
            FIELD_INTEGER: <?php echo FIELD_INTEGER; ?>,
            FIELD_FILE: <?php echo FIELD_FILE; ?>,
            FIELD_DATETIME: <?php echo FIELD_DATETIME; ?>,
            FIELD_LINK: <?php echo FIELD_LINK; ?>,
            FIELD_LINKSET: <?php echo FIELD_LINKSET; ?>,
            FIELD_MATSET: <?php echo FIELD_MATSET; ?>,
            FIELD_BOOLEAN: <?php echo FIELD_BOOLEAN; ?>,
            FIELD_ENUM: <?php echo FIELD_ENUM; ?>,
            FIELD_FORM: <?php echo FIELD_FORM; ?>,
            FIELD_MATERIAL: <?php echo FIELD_MATERIAL; ?>,
            FIELD_HUGETEXT: <?php echo FIELD_HUGETEXT; ?>,
            FIELD_DOUBLE: <?php echo FIELD_DOUBLE; ?>,
            PSEUDO_FIELD_FILESET: <?php echo PSEUDO_FIELD_FILESET; ?>,
            PSEUDO_FIELD_LINK_USER: <?php echo PSEUDO_FIELD_LINK_USER; ?>,
            PSEUDO_FIELD_LINKSET_USER: <?php echo PSEUDO_FIELD_LINKSET_USER; ?>,
            PSEUDO_FIELD_TAGS: <?php echo PSEUDO_FIELD_TAGS; ?>,
            PSEUDO_FIELD_CATOLOGS: <?php echo PSEUDO_FIELD_CATOLOGS; ?>
        },
        
        editors: {
            EDITOR_USER: <?php echo EDITOR_USER; ?>
        },
		
		permissions: {
			PERM_CAT_ADMIN: <?php echo PERM_CAT_ADMIN; ?>
		},
        
        // Массив редакторов полей
        fieldEditors: [],
        
        // Соответствие редакторов и полей
        fields_fieldEditors: [],
        
        fieldTypes : [
            <?
            $f = 1;
            foreach ($l_field_types as $i => $arr) {
                if (!$f) print ','; $f = 0;
                print "[".$i.",'".$arr."']\n";
            }
            ?>	
        ],
		
		userObjectGridFields: [
		<?php foreach(\Cetera\User::getObjectDefinition()->getFields() as $field) : ?>
			<?php if (in_array($field['name'],['disabled', 'password'])) continue; ?>
			<?php if (is_subclass_of($field, 'Cetera\ObjectFieldLinkAbstract')) continue; ?>
			{
				name: '<?php echo $field['name']; ?>',
				describ: '<?php echo $field['describ']; ?>',
				type: <?php echo (int)$field['type']; ?>,
				fixed: <?php echo (int)$field['fixed']; ?>
			},
		<?php endforeach; ?>
		],
        
        widgets: [
			<?php
			$f = true;
			foreach ($application->getRegisteredWidgets() as $item) {
				if (isset($item['not_placeable'])) continue;
				if (!$f) print ',';
				print "{\n";
				print '    icon:    "'.$item['icon']."\",\n";
				print '    name:    "'.$item['name']."\",\n";
				print '    describ: "'.$item['describ']."\",\n";   
				print '    ui:      "'.((isset($item['ui']))?$item['ui']:'')."\"\n";     
				print "}";
				$f = false;
			}
			?>        
        ],
		
		Lang: {},
		
	setLocale: function(locale,callback) {
		var url = Ext.util.Format.format("/cms/lang/ext-lang-{0}.js", locale);
		Ext.Loader.loadScript(url);

		Ext.Ajax.request({
			url: '/cms/lang/data.php?locale='+locale,
			success: function(response, opts) {
				Config.Lang = Ext.decode(response.responseText);

				Ext.apply(Ext.form.VTypes, {
						num:  function(v) {
							return /^\d+$/.test(v);
						},
						numText: Config.Lang.num,
						numMask: /[\d]/i
				});	

				if (callback) callback();
			}
		});             
	}		
         
}
<?php 
foreach($l_editors as $eid => $value) print "Config.fieldEditors[".$eid."]='".addslashes($value)."';\n";
foreach($field_editors as $fid => $value) print "Config.fields_fieldEditors[".$fid."] = [".implode(', ', $value)."];\n";
?>

<?php foreach(\Cetera\Theme::enum() as $theme) : ?>
Ext.Loader.setPath('Theme.<?php echo $theme->name; ?>', '/<?php echo THEME_DIR.'/'.$theme->name; ?>/ext');
<?php endforeach; ?>

<?php foreach(\Cetera\Plugin::enum() as $plugin) : ?>
    Ext.Loader.setPath('Plugin.<?php echo $plugin->name; ?>', '<?php echo $plugin->getUrlPath(); ?>ext');
<?php endforeach; ?>

function _(key) {
	if (Config.Lang[key]) return Config.Lang[key];
	return key;
}