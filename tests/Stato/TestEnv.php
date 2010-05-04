<?php

namespace Stato;

class TestEnv
{
    private static $tables;
    
    public static function getDbConnection()
    {
        $config = self::getDbConfig();
        $conn = new Dbal\Connection($config);
        return $conn;
    }
    
    public static function getDbConfig()
    {
        $config = array(
            'driver'   => $GLOBALS['db_driver'],
            'host'     => $GLOBALS['db_host'],
            'user'     => $GLOBALS['db_user'],
            'password' => $GLOBALS['db_password'],
            'dbname'   => $GLOBALS['db_name'],
            'dsn'      => $GLOBALS['db_dsn']
        );
        
        return $config;
    }
    
    public static function getSmtpConfig()
    {
        if (!isset($GLOBALS['smtp_host'])) return false;
        
        $config = array(
            'host' => $GLOBALS['smtp_host'],
            'port' => $GLOBALS['smtp_port'],
            'ssl'  => $GLOBALS['smtp_ssl'],
            'auth' => $GLOBALS['smtp_auth'],
            'username' => $GLOBALS['smtp_username'],
            'password' => $GLOBALS['smtp_password'],
        );
        
        return $config;
    }
    
    public static function getDbSchema()
    {
        if (!isset(self::$tables)) self::$tables = include_once __DIR__ . '/Dbal/files/schema.php';
        return self::$tables;
    }
    
    public static function createTestDatabase()
    {
        $config = self::getDbConfig();

        $tmpConf = $config;
        $tmpConf['dbname'] = null;
        $tmpConf['dsn'] = $tmpConf['driver'].':host='.$tmpConf['host'];

        $tmpConn = new Dbal\Connection($tmpConf);
        $tmpConn->dropDatabase($config['dbname']);
        $tmpConn->createDatabase($config['dbname']);
        $tmpConn->close();
        
        $conn = new Dbal\Connection($config);
        
        foreach (self::getDbSchema() as $table) {
            $conn->createTable($table);
        }
        $conn->close();
    }
    
    public static function emptyTestDatabase()
    {
        $conn = self::getDbConnection();
        foreach (self::getDbSchema() as $table) {
            $conn->truncateTable($table);
        }
    }
}
