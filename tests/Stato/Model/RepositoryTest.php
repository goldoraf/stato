<?php

namespace Stato\Model;

require_once __DIR__ . '/TestsHelper.php';

use FooMetaclass;
use Stato\TestEnv;

class RepositoryTest extends TestCase
{
    public function setup()
    {
        Repository::setup('test', TestEnv::getDbConfig());
        $this->repository = Repository::get('test');
        $this->repository->getAdapter()->setStorageNamingConvention(function($className) { return strtoupper($className); });
        $this->metaclass = new FooMetaclass;
        $this->repository->addMetaclass($this->metaclass);
    }
    
    public function testGetDefaultName()
    {
        $this->assertEquals('default', Repository::getDefaultName());
    }
    
    public function testGetMetaclass()
    {
        $this->assertSame($this->metaclass, $this->repository->getMetaclass('Foo'));
    }
    
    public function testGetModelClassFromCollectionName()
    {
        $this->assertEquals('Foo', $this->repository->getModelClass('FOO'));
    }
    
    public function testFrom()
    {
        $this->assertTrue($this->repository->from('FOO') instanceof Dataset);
    }
}