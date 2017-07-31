<?php
namespace Cetera;
include('../include/common_bo.php');

$t = $application->getTranslator();

$l_schema_errors = array(
	Schema::TABLE_NOT_EXISTS   => $t->_('таблица не найдела'),
	Schema::TABLE_WRONG_ENGINE => $t->_('неверный тип таблицы'),
	Schema::FIELD_NOT_FOUND    => $t->_('не найдено поле'),
	Schema::EXTRA_FIELD		   => $t->_('лишнее поле'),
	Schema::FIELD_DONT_MATCH   => $t->_('неверное поле'),
	Schema::EXTRA_KEY          => $t->_('лишний индекс'),
	Schema::KEY_NOT_FOUND      => $t->_('не найден индекс'),
	Schema::KEY_DONT_MATCH     => $t->_('неверный индекс'),
    Schema::TYPE_NOT_EXISTS    => $t->_('не найден тип материалов'),
    Schema::TYPE_FIELD_NOT_FOUND  => $t->_('не найдено поле'),
    Schema::TYPE_FIELD_DONT_MATCH => $t->_('неверное поле'),
	Schema::WIDGET_NOT_EXISTS     => $t->_('Не найден виджет'),
);

$res = array(
  	'success' => true, 
  	'text' => false
); 

if ($_REQUEST['db_structure']) {
    $schema = new Schema();   
    
    $result = $schema->compare_schemas($_REQUEST['ignore_fields'], $_REQUEST['ignore_keys']);
    
  	if (sizeof($result)) {
  	 	$res['success'] = false;
  		$msg = '';
  		$module = '';
  		$table = '';
  		foreach ($result as $error) {
  			if ($module != $error['module']) $msg .= '<b><u>'.$t->_('Модуль').' '.$error['module'].'</u></b><br />';
  			if ($table != $error['table']) $msg .= '<div class="hdr">'.$t->_('Таблица').' <b>'.$error['table'].'</b><div/>';
  			$msg .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$l_schema_errors[$error['error']];
        if (isset($error['widget'])) $msg .= ' <b>"'.$error['widget'].'"</b>';
  			if (isset($error['field'])) $msg .= ' <b>"'.$error['field'].'"</b>';
  			if (isset($error['expected'])) $msg .= ': <i>'.$error['found'].'</i> '.$t->_('ожилалось').': <i>'.$error['expected'].'</i>';
  			$msg .= '<br />';
  			$module = $error['module'];
  			$table = $error['table'];
  		}
  		$res['text'] = '<span class="error">'.$t->_('Обнаружены ошибки в структуре БД').'</span><div class="note">'.$msg.'</div>'; 
  	}
}

if ($_REQUEST['cat_structure']) {
	
	$errorText = '';

    $l_rule_broken	 = $t->_('Нарушено правило').': ';
    $l_rule1 		 = $t->_('Левый ключ ВСЕГДА меньше правого');
    $l_rule2 		 = $t->_('Наименьший левый ключ ВСЕГДА равен 1');
    $l_rule3 		 = $t->_('Наибольший правый ключ ВСЕГДА равен двойному числу узлов');
    $l_rule4 		 = $t->_('Разница между правым и левым ключом ВСЕГДА нечетное число');
    $l_rule5 		 = $t->_('Если уровень узла нечетное число то тогда левый ключ ВСЕГДА нечетное число, то же самое и для четных чисел');
    $l_rule6 		 = $t->_('Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый');
	
	// Левый ключ ВСЕГДА меньше правого
	$query = 'SELECT COUNT(*) FROM dir_structure WHERE lft >= rght';
	$r = $application->getConn()->fetchArray( 'SELECT COUNT(*) FROM dir_structure WHERE lft >= rght');
	if ($r[0] > 0) $errorText .= $l_rule_broken.$l_rule1.'<br />';
	
	// Наименьший левый ключ ВСЕГДА равен 1
	$r = $application->getConn()->fetchArray( 'SELECT MIN(lft) FROM dir_structure');
	if ($r[0] != 1) $errorText .= $l_rule_broken.$l_rule2.'<br />';

	// Наибольший правый ключ ВСЕГДА равен двойному числу узлов
	$r = $application->getConn()->fetchArray('SELECT MAX(rght) - 2*COUNT(*) FROM dir_structure');
	if ($r[0] != 0) $errorText .= $l_rule_broken.$l_rule3.'<br />';
	
	// Разница между правым и левым ключом ВСЕГДА нечетное число
	$r = $application->getConn()->fetchArray('SELECT SUM(IF(MOD(rght-lft,2)=1,0,1)) FROM dir_structure');
	if ($r[0] > 0) $errorText .= $l_rule_broken.$l_rule4.'<br />';
	
	// Если уровень узла нечетное число то тогда левый ключ ВСЕГДА нечетное число, то же самое и для четных чисел
	$r = $application->getConn()->fetchArray('SELECT SUM(IF(MOD(lft,2)+MOD(level,2)=1,0,1)) FROM dir_structure');
	if ($r[0] > 0) $errorText .= $l_rule_broken.$l_rule5.'<br />';
	
	// Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый
	$r = $application->getConn()->fetchArray('SELECT count(*) FROM dir_structure a, dir_structure b WHERE a.lft=b.rght or (a.lft=b.lft and a.id<>b.id) or (a.rght=b.rght and a.id<>b.id)');
	if ($r[0] > 0) $errorText .= $l_rule_broken.$l_rule6.'<br />';
	
	if ($errorText) {
		$res['text'] .= '<b class="error">'.$t->_('Обнаружены ошибки в структуре разделов').'</b><div class="note">'.$errorText.'</div>';
		$res['success'] = false;
	}
	
}

echo json_encode($res); 