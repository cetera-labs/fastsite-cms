<?
/**
 * Fastsite CMS 3 
 *
 * @package FastsiteCMS
 * @version $Id$
 * @author unknown 
 **/
 
namespace Cetera; 

/**
 * Nested Sets 
 *
 * @package FastsiteCMS
 * @access private 
 * @internal
 **/
class CDBTree {
	
	use DbConnection;
	
    /** Table with Nested Sets implemented */
	private $table;
	/** Name of the ID-auto_increment-field in the table */
	private $id;

	private $left = 'lft';
	private $right = 'rght';
	private $level = 'level';
    private $parent_id = 'parent_id';

	private $qryParams = '';
	private $qryFields = '';
	private $qryTables = '';
	private $qryWhere = '';
	private $qryGroupBy = '';
	private $qryHaving = '';
	private $qryOrderBy = '';
	private $qryLimit = '';
	private $sqlNeedReset = true;
	private $sql;

    /**  
     * Constructor
     * 
     * @param string $tableName table in database where to implement nested sets 
     * @param string $itemId name of the field which will uniquely identify every record
     * @param array  $fieldNames optional configuration array to set field names. Example:
     *			 array(
     *					'left' => 'cat_left',
     *					'right' => 'cat_right', 
     *					'level' => 'cat_level'
     *			 )                 
     * @return void   
     * @throws Exception      
     */
	function __construct($tableName, $itemId = 'id', $fieldNames=array()) {
		if(empty($tableName) || empty($itemId)) throw new Exception\CMS("phpDbTree error");
		$this->table = $tableName;
		$this->id = $itemId;
		if(is_array($fieldNames) && sizeof($fieldNames)) 
			foreach($fieldNames as $k => $v)
				$this->$k = $v;
	}

    /**  
     * Returns a Left and Right IDs and Level of an element or false on error
     * 
     * @param int $ID an ID of the element               
     * @return array    
     */
	function getNodeInfo($ID) {
		$this->sql = 'SELECT '.$this->left.','.$this->right.','.$this->level.' FROM '.$this->table.' WHERE '.$this->id.'=\''.$ID.'\'';
        $Data = $this->getDbConnection()->fetchAssoc($this->sql);
		if ($Data)
			return array((int)$Data[$this->left], (int)$Data[$this->right], (int)$Data[$this->level]);
		else
        	return FALSE;
	}

    /**  
     * Inserts a record into the table with nested sets
     * 
     * @param int $ID an ID of the parent element     
     * @param array $data array with data to be inserted: array(<field_name> => <field_value>)              
     * @return bool true on success, or false on error    
     */
	function insert($ID, $data) {
		if(!(list($leftId, $rightId, $level) = $this->getNodeInfo($ID)))
        	return FALSE;

		// preparing data to be inserted
		if(sizeof($data)) {
			$fld_names = implode(',', array_keys($data)).',';
			$fld_values = '\''.implode('\',\'', array_values($data)).'\',';
		}
		$fld_names .= $this->left.','.$this->right.','.$this->level.','.$this->parent_id;
		$fld_values .= ($rightId).','.($rightId+1).','.($level+1).','.($ID);

		// creating a place for the record being inserted
		if($ID) {
			$this->sql = 'UPDATE IGNORE '.$this->table.' SET '
				. $this->left.'=IF('.$this->left.'>'.$rightId.','.$this->left.'+2,'.$this->left.'),'
				. $this->right.'=IF('.$this->right.'>='.$rightId.','.$this->right.'+2,'.$this->right.')'
				. 'WHERE '.$this->right.'>='.$rightId;
			$this->getDbConnection()->executeQuery($this->sql);
		}

		// inserting new record
		$this->sql = 'INSERT INTO '.$this->table.'('.$fld_names.') VALUES('.$fld_values.')';
        $this->getDbConnection()->executeQuery($this->sql);

		return $this->getDbConnection()->lastInsertId();
	}

    /**  
     * Assigns a node with all its children to another parent
     * 
     * @param int $ID node ID     
     * @param int $newParentID ID of new parent node              
     * @return bool false on error    
     */
	function moveAll($ID, $newParentId) {
		if(!(list($leftId, $rightId, $level) = $this->getNodeInfo($ID))) return FALSE;
		if(!(list($leftIdP, $rightIdP, $levelP) = $this->getNodeInfo($newParentId))) return FALSE;
		if($ID == $newParentId || $leftId == $leftIdP || ($leftIdP >= $leftId && $leftIdP <= $rightId)) return false;

      // ���� ���������� ����������� ����� �� ������������ ���
      if ($leftIdP < $leftId && $rightIdP > $rightId && $levelP < $level - 1 ) {
         $this->sql = 'UPDATE '.$this->table.' SET '
            . $this->level.'=IF('.$this->left.' BETWEEN '.$leftId.' AND '.$rightId.', '.$this->level.sprintf('%+d', -($level-1)+$levelP).', '.$this->level.'), '
            . $this->right.'=IF('.$this->right.' BETWEEN '.($rightId+1).' AND '.($rightIdP-1).', '.$this->right.'-'.($rightId-$leftId+1).', '
                           .'IF('.$this->left.' BETWEEN '.($leftId).' AND '.($rightId).', '.$this->right.'+'.((($rightIdP-$rightId-$level+$levelP)/2)*2 + $level - $levelP - 1).', '.$this->right.')),  '
            . $this->left.'=IF('.$this->left.' BETWEEN '.($rightId+1).' AND '.($rightIdP-1).', '.$this->left.'-'.($rightId-$leftId+1).', '
                           .'IF('.$this->left.' BETWEEN '.$leftId.' AND '.($rightId).', '.$this->left.'+'.((($rightIdP-$rightId-$level+$levelP)/2)*2 + $level - $levelP - 1).', '.$this->left. ')) '
            . 'WHERE '.$this->left.' BETWEEN '.($leftIdP+1).' AND '.($rightIdP-1)
         ;
      } elseif($leftIdP < $leftId) {
         $this->sql = 'UPDATE '.$this->table.' SET '
            . $this->level.'=IF('.$this->left.' BETWEEN '.$leftId.' AND '.$rightId.', '.$this->level.sprintf('%+d', -($level-1)+$levelP).', '.$this->level.'), '
            . $this->left.'=IF('.$this->left.' BETWEEN '.$rightIdP.' AND '.($leftId-1).', '.$this->left.'+'.($rightId-$leftId+1).', '
               . 'IF('.$this->left.' BETWEEN '.$leftId.' AND '.$rightId.', '.$this->left.'-'.($leftId-$rightIdP).', '.$this->left.') '
            . '), '
            . $this->right.'=IF('.$this->right.' BETWEEN '.$rightIdP.' AND '.$leftId.', '.$this->right.'+'.($rightId-$leftId+1).', '
               . 'IF('.$this->right.' BETWEEN '.$leftId.' AND '.$rightId.', '.$this->right.'-'.($leftId-$rightIdP).', '.$this->right.') '
            . ') '
            . 'WHERE '.$this->left.' BETWEEN '.$leftIdP.' AND '.$rightId
            // !!! added this line (Maxim Matyukhin)
            .' OR '.$this->right.' BETWEEN '.$leftIdP.' AND '.$rightId
         ;
      } else {
         $this->sql = 'UPDATE '.$this->table.' SET '
            . $this->level.'=IF('.$this->left.' BETWEEN '.$leftId.' AND '.$rightId.', '.$this->level.sprintf('%+d', -($level-1)+$levelP).', '.$this->level.'), '
            . $this->left.'=IF('.$this->left.' BETWEEN '.$rightId.' AND '.$rightIdP.', '.$this->left.'-'.($rightId-$leftId+1).', '
               . 'IF('.$this->left.' BETWEEN '.$leftId.' AND '.$rightId.', '.$this->left.'+'.($rightIdP-1-$rightId).', '.$this->left.')'
            . '), '
            . $this->right.'=IF('.$this->right.' BETWEEN '.($rightId+1).' AND '.($rightIdP-1).', '.$this->right.'-'.($rightId-$leftId+1).', '
               . 'IF('.$this->right.' BETWEEN '.$leftId.' AND '.$rightId.', '.$this->right.'+'.($rightIdP-1-$rightId).', '.$this->right.') '
            . ') '
            . 'WHERE '.$this->left.' BETWEEN '.$leftId.' AND '.$rightIdP
            // !!! added this line (Maxim Matyukhin)
            . ' OR '.$this->right.' BETWEEN '.$leftId.' AND '.$rightIdP
         ;
      }

		$this->getDbConnection()->executeQuery($this->sql);
		return true;
	}

    /**  
     * Deletes a record wihtout deleting its children
     * 
     * @param int $ID an ID of the element to be deleted               
     * @return bool true on success, or false on error    
     */
	function delete($ID) {
		if(!(list($leftId, $rightId, $level) = $this->getNodeInfo($ID))) return FALSE;

		// Deleting record
		$this->sql = 'DELETE FROM '.$this->table.' WHERE '.$this->id.'=\''.$ID.'\'';
		$this->getDbConnection()->executeQuery($this->sql);

		// Clearing blank spaces in a tree
		$this->sql = 'UPDATE '.$this->table.' SET '
			. $this->left.'=IF('.$this->left.' BETWEEN '.$leftId.' AND '.$rightId.','.$this->left.'-1,'.$this->left.'),'
			. $this->right.'=IF('.$this->right.' BETWEEN '.$leftId.' AND '.$rightId.','.$this->right.'-1,'.$this->right.'),'
			. $this->level.'=IF('.$this->left.' BETWEEN '.$leftId.' AND '.$rightId.','.$this->level.'-1,'.$this->level.'),'
			. $this->left.'=IF('.$this->left.'>'.$rightId.','.$this->left.'-2,'.$this->left.'),'
			. $this->right.'=IF('.$this->right.'>'.$rightId.','.$this->right.'-2,'.$this->right.') '
			. 'WHERE '.$this->right.'>'.$leftId
		;
		$this->getDbConnection()->executeQuery($this->sql);

		return true;
	}

    /**  
     * Deletes a record with all its children
     * 
     * @param int $ID an ID of the element to be deleted               
     * @return bool true on success, or false on error    
     */
	function deleteAll($ID) {
		if(!(list($leftId, $rightId, $level) = $this->getNodeInfo($ID))) return FALSE;

		// Deleteing record(s)
		$this->sql = 'DELETE FROM '.$this->table.' WHERE '.$this->left.' BETWEEN '.$leftId.' AND '.$rightId;
		$this->getDbConnection()->executeQuery($this->sql);

		// Clearing blank spaces in a tree
		$deltaId = ($rightId - $leftId)+1;
		$this->sql = 'UPDATE '.$this->table.' SET '
			. $this->left.'=IF('.$this->left.'>'.$leftId.','.$this->left.'-'.$deltaId.','.$this->left.'),'
			. $this->right.'=IF('.$this->right.'>'.$leftId.','.$this->right.'-'.$deltaId.','.$this->right.') '
			. 'WHERE '.$this->right.'>'.$rightId
		;
		$this->getDbConnection()->executeQuery($this->sql);

		return TRUE;
	}

}