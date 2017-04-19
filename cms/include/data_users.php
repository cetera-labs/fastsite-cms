<?php
namespace Cetera;
/************************************************************************************************

Список материалов

*************************************************************************************************/

include('common_bo.php');

$data = array();
$group = '';

if (!isset($_REQUEST['sort'])) {
    $_REQUEST['sort'] = 'login';
    $_REQUEST['dir'] = 'ASC';
}

if (isset($_REQUEST['gid']))
    $group = ((isset($_REQUEST['filter']) && $_REQUEST['filter'])?' INNER':' LEFT').' JOIN users_groups_membership C ON (A.id = C.user_id and C.group_id='.(int)$_REQUEST['gid'].(($_REQUEST['gid']==GROUP_ADMIN || $_REQUEST['gid']==GROUP_BACKOFFICE)?' or A.id=1':'').') ';
      
$query = '
    SELECT SQL_CALC_FOUND_ROWS 
      A.*, 
      SUM(IF(B.group_id='.GROUP_BACKOFFICE.' or B.group_id='.GROUP_ADMIN.' or A.id=1,1,0)) as bo'.
      (isset($_REQUEST['gid'])?', COUNT(C.group_id) as checked':'').'
    FROM users A 
    LEFT JOIN users_groups_membership B ON (A.id = B.user_id)
    '.$group.'
    WHERE A.id<>0'
          .(($_REQUEST['bo']=='true')?' and (A.id = 1 or B.group_id='.GROUP_BACKOFFICE.' or B.group_id='.GROUP_ADMIN.')':'')
          .((isset($_REQUEST['query']) && $_REQUEST['query'])?' and (A.email LIKE "%'.mysql_escape_string($_REQUEST['query']).'%" or A.login LIKE "%'.mysql_escape_string($_REQUEST['query']).'%" or A.name LIKE "%'.mysql_escape_string($_REQUEST['query']).'%")':'').'
    GROUP BY A.id
    ORDER BY '.mysql_escape_string($_REQUEST['sort']).' '.mysql_escape_string($_REQUEST['dir']);
 
if (isset($_REQUEST['start']) && isset($_REQUEST['limit']))
    $query .= ' LIMIT '.(int)$_REQUEST['start'].','.(int)$_REQUEST['limit'];

$r = fssql_query($query);

while ($f = mysql_fetch_assoc($r)) {
    $f['bo'] = (boolean)$f['bo'];
    if (isset($f['checked'])) $f['checked'] = (boolean)$f['checked'];
    $f['disabled'] = (boolean)$f['disabled'];
    $data[] = $f;
}

$total = mysql_result(fssql_query('SELECT FOUND_ROWS()'),0);

echo json_encode(array(
    'success' => true,
    'total'   => $total,
    'rows'    => $data
));
?>
