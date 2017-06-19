<?php

use PHPUnit\Framework\TestCase;

class BliskapaczkaXmlTest extends TestCase
{
    protected $filePath;

    protected function setUp()
    {
        $this->filePath = $GLOBALS['APP_DIR'] . '/design/frontend/base/default/layout/sendit/bliskapaczka.xml';
    }

    public function testFileExists()
    {
        $this->assertTrue(file_exists($this->filePath));
    }
}
