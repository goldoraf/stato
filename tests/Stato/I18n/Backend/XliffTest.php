<?php

namespace Stato\I18n\Backend;

use Stato\I18n\I18n;
use Stato\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class XliffTest extends YamlTest
{
    public function setup()
    {
        $this->backend = new Xliff(__DIR__ . '/../data/xliff');
    }
}