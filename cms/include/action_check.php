<?php
namespace Cetera;
include('../include/common_bo.php');

$l_schema_errors = array(
	Schema::TABLE_NOT_EXISTS   => 'таблица не найдела',
	Schema::TABLE_WRONG_ENGINE => 'неверный тип таблицы',
	Schema::FIELD_NOT_FOUND    => 'не найдено поле',
	Schema::EXTRA_FIELD		     => 'лишнее поле',
	Schema::FIELD_DONT_MATCH   => 'неверное поле',
	Schema::EXTRA_KEY          => 'лишний индекс',
	Schema::KEY_NOT_FOUND      => 'не найден индекс',
	Schema::KEY_DONT_MATCH     => 'неверный индекс',
    Schema::TYPE_NOT_EXISTS    => 'не найден тип материалов',
    Schema::TYPE_FIELD_NOT_FOUND  => 'не найдено поле',
    Schema::TYPE_FIELD_DONT_MATCH => 'неверное поле',
	Schema::WIDGET_NOT_EXISTS     => 'Не найден виджет',
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
  			if ($module != $error['module']) $msg .= '<b><u>Модуль '.$error['module'].'</u></b><br />';
  			if ($table != $error['table']) $msg .= '<div class="hdr">Таблица <b>'.$error['table'].'</b><div/>';
  			$msg .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$l_schema_errors[$error['error']];
        if (isset($error['widget'])) $msg .= ' <b>"'.$error['widget'].'"</b>';
  			if (isset($error['field'])) $msg .= ' <b>"'.$error['field'].'"</b>';
  			if (isset($error['expected'])) $msg .= ': <i>'.$error['found'].'</i> ожилалось: <i>'.$error['expected'].'</i>';
  			$msg .= '<br />';
  			$module = $error['module'];
  			$table = $error['table'];
  		}
  		$res['text'] = '<span class="error">Обнаружены ошибки в структуре БД</span><div class="note">'.$msg.'</div>'; 
  	}
}

if ($_REQUEST['cat_structure']) {
	
	$errorText = '';

    $l_rule_broken	 = 'Нарушено правило: ';
    $l_rule1 		 = 'Левый ключ ВСЕГДА меньше правого';
    $l_rule2 		 = 'Наименьший левый ключ ВСЕГДА равен 1';
    $l_rule3 		 = 'Наибольший правый ключ ВСЕГДА равен двойному числу узлов';
    $l_rule4 		 = 'Разница между правым и левым ключом ВСЕГДА нечетное число';
    $l_rule5 		 = 'Если уровень узла нечетное число то тогда левый ключ ВСЕГДА нечетное число, то же самое и для четных чисел';
    $l_rule6 		 = 'Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый';
	
	// Левый ключ ВСЕГДА меньше правого
	$query = 'SELECT COUNT(*) FROM dir_structure WHERE lft >= rght';
	$r = mysql_query($query);
	if ($r && mysql_result($r,0) > 0) $errorText .= $l_rule_broken.$l_rule1.'<br />';
	
	// Наименьший левый ключ ВСЕГДА равен 1
	$query = 'SELECT MIN(lft) FROM dir_structure';
	$r = mysql_query($query);
	if ($r && mysql_result($r,0) != 1) $errorText .= $l_rule_broken.$l_rule2.'<br />';

	// Наибольший правый ключ ВСЕГДА равен двойному числу узлов
	$query = 'SELECT MAX(rght) - 2*COUNT(*) FROM dir_structure';
	$r = mysql_query($query);
	if ($r && mysql_result($r,0) != 0) $errorText .= $l_rule_broken.$l_rule3.'<br />';
	
	// Разница между правым и левым ключом ВСЕГДА нечетное число
	$query = 'SELECT SUM(IF(MOD(rght-lft,2)=1,0,1)) FROM dir_structure';
	$r = mysql_query($query);
	if ($r && mysql_result($r,0) > 0) $errorText .= $l_rule_broken.$l_rule4.'<br />';
	
	// Если уровень узла нечетное число то тогда левый ключ ВСЕГДА нечетное число, то же самое и для четных чисел
	$query = 'SELECT SUM(IF(MOD(lft,2)+MOD(level,2)=1,0,1)) FROM dir_structure';
	$r = mysql_query($query);
	if ($r && mysql_result($r,0) > 0) $errorText .= $l_rule_broken.$l_rule5.'<br />';
	
	// Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый
	$query = 'SELECT count(*) FROM dir_structure a, dir_structure b WHERE a.lft=b.rght or (a.lft=b.lft and a.id<>b.id) or (a.rght=b.rght and a.id<>b.id)';
	$r = mysql_query($query);
	if ($r && mysql_result($r,0) > 0) $errorText .= $l_rule_broken.$l_rule6.'<br />';
	
	if ($errorText) {
		$res['text'] .= '<b class="error">Обнаружены ошибки в структуре разделов</b><div class="note">'.$errorText.'</div>';
		$res['success'] = false;
	}
	
}

echo json_encode($res); 