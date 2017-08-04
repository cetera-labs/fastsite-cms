<?php
namespace Cetera;
/************************************************************************************************

Список материалов

*************************************************************************************************/

try {
   
    include('common_bo.php');
    
    Material::clearLocks();
    
    if (isset($_REQUEST['id']) && $_REQUEST['id'])
	{    
        $m_id     = (int)$_REQUEST['id'];
        $m_realid = (int)$_REQUEST['id'];
        $catalog = Catalog::getById($m_id);
        
        if ($_REQUEST['math_subs'])
		{
          $where = 'and A.idcat IN ('.implode(',',$catalog->subs).')';
        }
		else
		{
          $where = "and A.idcat=$m_realid";
        }
        
        // Выяснение прав пользователя ------------
        $right[0] = $user->allowCat(PERM_CAT_OWN_MAT, $m_realid); // Pабота со своими материалами
        $right[1] = $user->allowCat(PERM_CAT_ALL_MAT, $m_realid); // Работа с материалами других авторов
        
        if (!$right[1]) $where .= ' and A.autor = '.$user->id; 
        
        $math = $catalog->materialsTable;
        $type = $catalog->materialsType;
        
    }
	elseif (isset($_REQUEST['type']) && $_REQUEST['type'])
	{    
        $od = new ObjectDefinition($_REQUEST['type']);   		
        $math = $od->table;
        $type = $od->id;        
        $where = '';   
		$right = [1,1];		
    }
	else
	{
        throw new Exception\CMS(Exception\CMS::INVALID_PARAMS);
    }
	
	if (isset($_REQUEST['filter'])) {
		$where .= ' and '.$_REQUEST['filter'];
	}
    
    if (!isset($_REQUEST['fields']))
        $fields = array('name');
        else $fields = $_REQUEST['fields'];
    
    if (!isset($_REQUEST['query'])) $_REQUEST['query'] = '';
    $query = '%'.$_REQUEST['query'].'%';
    
    $math_at_once = $_REQUEST['limit'];
    $m_first = $_REQUEST['start'];
    
    
    $order = $_REQUEST['dir'];
    $sort = $_REQUEST['sort'];
	if (strpos($sort, '.')===false)
	{
		$sort = 'A.'.$sort;
	}
    
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   A.id, A.tag, A.type, A.autor as autor_id, UNIX_TIMESTAMP(A.dat) as dat, 
                   IF(C.name<>'' and C.name IS NOT NULL, C.name, C.login) as autor, 
                   A.alias, D.user_id as locked, F.login as locked_login, A.".implode(', A.', $fields).",
				   E.name as catalog
            FROM $math A 
			LEFT JOIN `users` C ON (A.autor=C.id) 
			LEFT JOIN `lock` D ON (A.id = D.material_id and D.type_id=$type AND D.dat >= NOW()-INTERVAL 10 SECOND) 
			LEFT JOIN `dir_data` E ON (A.idcat = E.id)
			LEFT JOIN `users` F ON (F.id = D.user_id)
            WHERE (A.name like '$query' or A.alias like '$query' or C.name like '$query' or C.login like '$query')
                  $where
            ORDER BY $sort $order
            LIMIT $m_first,$math_at_once";	
    $r = $application->getConn()->fetchAll($sql);
	//print $sql;
	
    $all_filter = $application->getConn()->fetchColumn('SELECT FOUND_ROWS()',[],0);
        
    $materials = array();
    
    foreach ($r as $f) {     
        $f['icon'] = ($f['type'] & MATH_PUBLISHED)?1:0;
        $f['disabled'] = $f['locked'] || !(($f['autor_id']==$user->id && $right[0])||($f['autor_id']!=$user->id && $right[1]));        
        $materials[] = $f;        
    }
    
    echo json_encode(array(
        'success' => true,
        'total'   => $all_filter,
        'rows'    => $materials
    ));
    
} catch (Exception $e) {

    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'rows'    => false
    ));

}
