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
        $widgetCss = false;
        $widgetCssHttps = false;
        $widgetCssV2 = false;
        $widgetJs = false;
        $widgetJsHttps = false;
        $widgetJsV2 = false;
        $moduleJs = false;
        $moduleCss = false;


        $blocks = $this->layout->checkout_bliskapaczka->reference->block;

        foreach ($blocks as $block) {
            if ($block->attributes()->name == 'styles_for_widget.bliskapaczka.pl') {
                $widgetCss = true;
                if (strpos((string)$block->action->text, 'https://') !== false) {
                    $widgetCssHttps = true;
                }

                if (strpos((string)$block->action->text, 'v5') !== false) {
                    $widgetCssV2 = true;
                }
            }

            if ($block->attributes()->name == 'widget.bliskapaczka.pl') {
                $widgetJs = true;
                if (strpos((string)$block->action->text, 'https://') !== false) {
                    $widgetJsHttps = true;
                }

                if (strpos((string)$block->action->text, 'v5') !== false) {
                    $widgetJsV2 = true;
                }
            }
        }

        $actions = $this->layout->checkout_bliskapaczka->reference->action;

        foreach ($actions as $action) {
            if ($action->attributes()->method == 'addJs') {
                if (strpos((string)$action->script, 'sendit/bliskapaczka.js') !== false) {
                    $moduleJs = true;
                }
            }

            if ($action->attributes()->method == 'addCss') {
                if (strpos((string)$action->script, 'css/bliskapaczka.css') !== false) {
                    $moduleCss = true;
                }
            }
        }

        $this->assertTrue($widgetCss);
        $this->assertTrue($widgetCssHttps);
        $this->assertTrue($widgetCssV2);
        $this->assertTrue($widgetJs);
        $this->assertTrue($widgetJsHttps);
        $this->assertTrue($widgetJsV2);
        $this->assertTrue($moduleJs);
        $this->assertTrue($moduleCss);
    }
}
