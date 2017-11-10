<?php

use PHPUnit\Framework\TestCase;

class BliskapaczkaXmlTest extends TestCase
{
    protected $filePath;
    protected $layout;

    protected function setUp()
    {
        $this->filePath = $GLOBALS['APP_DIR'] . '/design/frontend/base/default/layout/sendit/bliskapaczka.xml';
        $this->layout = new SimpleXMLElement(file_get_contents($this->filePath));
    }

    public function testFileExists()
    {
        $this->assertTrue(file_exists($this->filePath));
    }

    public function testWidgetInHeader()
    {
        $css = false;
        $cssHttps = false;
        $cssV2 = false;
        $js = false;
        $jsHttps = false;
        $jsV2 = false;

        $blocks = $this->layout->default->reference->block;

        foreach ($blocks as $block) {
            if ($block->attributes()->name == 'styles_for_widget.bliskapaczka.pl') {
                $css = true;
                if (strpos((string)$block->action->text, 'https://') !== false) {
                    $cssHttps = true;
                }

                if (strpos((string)$block->action->text, 'v4') !== false) {
                    $cssV2 = true;
                }
            }

            if ($block->attributes()->name == 'widget.bliskapaczka.pl') {
                $js = true;
                if (strpos((string)$block->action->text, 'https://') !== false) {
                    $jsHttps = true;
                }

                if (strpos((string)$block->action->text, 'v4') !== false) {
                    $jsV2 = true;
                }
            }
        }

        $this->assertTrue($css);
        $this->assertTrue($cssHttps);
        $this->assertTrue($cssV2);
        $this->assertTrue($js);
        $this->assertTrue($jsHttps);
        $this->assertTrue($jsV2);
    }
}
