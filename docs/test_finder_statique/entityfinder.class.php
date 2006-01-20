<?php

class EntityFinder
{
    public static function find($type, $entity, $conditions=null, $options=array())
    {   
        switch($type)
        {
            case 'first':
                $options = array_merge($options, array('limit' => 1));
                self::find('all', $entity, $conditions, $options);
            case 'all':
                if (isset($options['include']))
            	{
                    //return $this->findWithAssociations($conditions, $options);
                }
                else
                {
                    $sql = self::prepareSelect($entity, $conditions, $options);
                    return self::findBySql($sql);
                }
        }
    }
    
    public static function findBySql($sql)
    {
        $db = Database::getInstance();
        $rs = $db->select($sql);
    	if (!$rs) return false;
        $set = array();
        //$class = $this->getTableName();
        while($row = $rs->fetch())
        {
            //$set[] = $this->instanciate($row);
            $set[] = $row;
        }
        //if (count($set) == 1) return $set[0];
        return $set;
    }
    
    public static function prepareSelect($entity, $conditions=null, $options=array())
    {
		$model = EntityManager::get($entity);
        $sql = 'SELECT * FROM '.$model->tableName;
		if ($conditions !== Null) $sql.= self::addConditions($conditions);
        if (isset($options['order'])) $sql.= ' ORDER BY '.$options['order'];
        if (isset($options['limit']))
        {
            $offset = 0;
            if (isset($options['offset'])) $offset = $options['offset'];
            $db = Database::getInstance();
            $sql.= $db->limit($options['limit'], $offset);
        }
		return $sql;
	}
	
	public static function addConditions($conditions)
	{
        if (is_array($conditions))
        {
            // to be continued
        }
        else
        {
            return ' WHERE '.$conditions;
        }
    }
}

?>
