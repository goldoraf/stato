<?php

namespace Stato\Orm;

class Session
{
    const PENDING = 1;
    const PERSISTENT = 2;
    const REMOVED = 3;
    const DETACHED = 4;
    
    private $metadata;
    private $connection;
    private $identityMap;
    private $instanceIdentifiers;
    private $instanceStates;
    private $new;
    private $deleted;
    
    public function __construct(Database $db)
    {
        $this->metadata = $db;
        $this->connection = $db->getConnection();
        $this->identityMap = array();
        $this->instanceIdentifiers = array();
        $this->instanceStates = array();
        $this->new = array();
        $this->deleted = array();
    }
    
    public function query($class)
    {
        $mapper = $this->metadata->getMapper($class);
        $table = $this->metadata->getTable($mapper->getTableName());
        return new Dataset($table, $this->connection, $mapper, $this);
    }
    
    public function save($instance)
    {
        $state = $this->getInstanceState($instance);
        $this->createOrUpdate($instance, $state);
    }
    
    public function flush()
    {
        $this->connection->beginTransaction();
        
        try {
            foreach ($this->new as $obj) {
                $mapper = $this->metadata->getMapper(get_class($obj));
                $id = $mapper->insertObject($obj, $this->connection);
                $this->attach($obj, $id);
            }
            
            $this->connection->commit();
            
            $this->new = array();
        } catch (\Exception $e) {
            $this->connection->rollback();
            throw $e;
        }
    }
    
    public function newInstance($className, array $values)
    {
        $mapper = $this->metadata->getMapper($className);
        $id = $values[$mapper->getIdentifier()];
        if (!isset($this->identityMap[$className][$id])) {
            $instance = new $className;
            $this->attach($instance, $id);
        }
        return $this->identityMap[$className][$id];
    }
    
    private function createOrUpdate($instance, $state)
    {
        if ($state == self::PENDING) $this->create($instance);
        else $this->update($instance);
    }
    
    private function create($instance)
    {
        $oid = spl_object_hash($instance);
        if (!array_key_exists($oid, $this->new)) $this->new[$oid] = $instance;
    }
    
    private function attach($instance, $id)
    {
        $oid = spl_object_hash($instance);
        $this->instanceIdentifiers[$oid] = $id;
        $this->instanceStates[$oid] = self::PERSISTENT;
        $this->identityMap[get_class($instance)][$id] = $instance;
    }
    
    private function getInstanceState($instance)
    {
        $oid = spl_object_hash($instance);
        if (!isset($this->instanceStates[$oid])) {
            $this->instanceStates[$oid] = self::PENDING;
        }
        return $this->instanceStates[$oid];
    }
}