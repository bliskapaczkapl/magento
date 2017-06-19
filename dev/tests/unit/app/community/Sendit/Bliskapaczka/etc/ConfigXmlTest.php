<?php

use PHPUnit\Framework\TestCase;

class ConfigXmlTest extends TestCase
{
    protected $filePath;
    protected $config;

    protected function setUp()
    {
        $this->filePath = $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/etc/config.xml';
        $this->config = new SimpleXMLElement(file_get_contents($this->filePath));
    }

    public function testFileExists()
    {
        $this->assertTrue(file_exists($this->filePath));
    }

    public function testBasicConfiguration()
    {
        $version = $this->config->modules->Sendit_Bliskapaczka->version;
        preg_match('/^([0-9]*)\.([0-9]*)\.([0-9]*)$/', $version, $match);

        $this->assertTrue(isset($match[0]));
    }

    public function testLayoutConfiguration()
    {
        $layout = $this->config->frontend->layout;

        $this->assertEquals('sendit/bliskapaczka.xml', $layout->updates->sendit_bliskapaczka->file);
    }
}
