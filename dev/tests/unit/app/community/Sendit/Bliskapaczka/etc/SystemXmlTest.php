<?php

use PHPUnit\Framework\TestCase;

class SystemXmlTest extends TestCase
{
    protected $filePath;

    protected function setUp()
    {
        $this->filePath = $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/etc/system.xml';
    }

    public function testFileExists()
    {
        $this->assertTrue(file_exists($this->filePath));
    }
}
