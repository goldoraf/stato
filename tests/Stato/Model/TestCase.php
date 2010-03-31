<?php

namespace Stato\Model;

use Stato\TestEnv;

class TestCase extends \Stato\TestCase
{
    public function setup()
    {
        TestEnv::emptyTestDatabase();
    }
}
