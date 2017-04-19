<?php
$tables = array(
	'core' => array(
		'materials', 'dir_data', 'dir_structure', 'field_link', 'server_aliases', 
        'types', 'types_fields', 
        'users_auth', 'users_groups', 'users_groups_membership', 'users_groups_allow_cat', 'users_groups_deny_filesystem'
	),
/*
	'stats' => array(
		'stats_agent', 'stats_agent_usage', 'stats_hosts', 'stats_isearch_usage', 'stats_log_days', 
		'stats_log_hours', 'stats_path', 'stats_ref_hosts', 'stats_ref_hosts_usage', 'stats_search', 
		'stats_search_usage', 'stats_sessions', 'stats_url', 'stats_url_usage', 
                'stats_url_points_in', 'stats_url_points_out'
	),
	'banner' => array(
		'banner_banners', 'banner_groups', 'banner_members', 'banner_hours', 
                'banner_country', 'banner_users', 
                'banner_adwords','banner_adwords_country','banner_adwords_dirs','banner_adwords_stats'
	),
	'forms' => array(
		'forms', 'form_fields', 'form_templates', 'form_users', 'form_errors', 'form_configs', 
		'form_fields_validators'
	),
	'forum' => array(
		'forum_allow', 'forum_answers', 'forum_deny', 'forum_forums', 'forum_groups', 'forum_replace', 
		'forum_replace_groups', 'forum_themes', 'forum_users_groups', 'forum_groups_ip'
	),
	'mail_lists' => array(
		'mail_lists', 'mail_list_history', 'userlist', 'main_list'
	),
	'sitemap' => array(
		'sitemap'
	),
	'site_users' => array(
		'site_users', 'site_users_server', 'site_users_server_groups'
	),
	'poll' => array(
		'vote', 'vote_servers', 'vote_history'
	),
	'webdav' => array(
		'webdav_properties', 'webdav_locks'
	),	
	'rss' => array(
		'rss'
	),	
	'google_sitemaps' => array(
		'gsitemaps'
	)
*/
);

error_reporting (E_ALL ^ E_NOTICE);
set_time_limit(9999);
include('../include/common.php');

if (isset($_GET['selected'])) 
	make_schemas($_GET['selected']);
	else menu();

//print_r(parse_schema(WWWROOT.'schema.xml'));

//print '<pre>'.create_table_by_schema(describe_table('dir_data'), 'cp1251').'</pre>';

function menu() {
	
	$schema = new Schema();
	
	print "<h1>Создать схемы БД</h1>\n<form>\n";
	foreach (array_keys($schema->schemas) as $key) if ($schema->schemas[$key]['schema'])
		print '<input type="checkbox" name="selected[]" value="'.$key.'" /> '.$schema->schemas[$key]['name']."<br />\n";
	print '<input type="submit" value="Создать" /></form>';
}

function make_schemas($selected) {
	global $tables;
	
	$schema = new Schema();
	
	foreach ($selected as $key) if ($schema->schemas[$key]['schema']) {
		print $schema->schemas[$key]['name'].' ... ';
		flush();
		
		$xml = '<?xml version="1.0"?>'."\n".'<schema>'."\n"."\n";
		if (is_array($tables[$key]))
			foreach($tables[$key] as $table) {
				$_xml = $schema->xmlTable($table);
				if (!$_xml) {
				    print '<font color="red">Ошибка при обработке таблицы <b>'.$table.'</b></font> ';
				} else $xml .= $_xml."\n";
			}
		$xml .= "\n</schema>";
		$f = fopen(CMSROOT.$schema->schemas[$key]['schema'], 'w');
		if ($f) {    
			fwrite($f, $xml);
			fclose($f);
			print "OK<br>\n";
		} else 
			print "Ошибка! Файл ".$schema->schemas[$key]['schema']." недоступен для записи\n";
		flush();
	}
	print '<hr />Готово';
}
?>
