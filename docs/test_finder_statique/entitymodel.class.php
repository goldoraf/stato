<?php

class EntityModel
{
    public $tableName = Null;
	public $entityName = Null;
	public $primaryKey = Null;
	
	public $fields = array();
	public $associations = array();
	
	public function __construct()
	{
		if ($this->entityName == Null)
            $this->entityName = str_replace('model', '', get_class($this));
        if ($this->tableName == Null) $this->tableName = $this->entityName;
        if ($this->primaryKey == null) return 'id';
        if (count($this->fields) == 0)
        {
            $db = Database::getInstance();
            $this->fields = $db->getColumns($this->tableName);
        }
	}
}

?>
