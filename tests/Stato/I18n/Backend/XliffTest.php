<?php

namespace Stato\I18n\Backend;

use Stato\I18n\I18n;
use Stato\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class XliffTest extends YamlTest
{
    public function setup()
    {
        I18n::addDataPath(__DIR__ . '/../data/xliff');
        $this->backend = new Xliff();
    }
}