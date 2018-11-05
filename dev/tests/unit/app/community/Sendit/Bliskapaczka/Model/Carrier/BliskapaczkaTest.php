<?php

require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Carrier/Abstract.php';
require $GLOBALS['APP_DIR'] . '/code/community/Sendit/Bliskapaczka/Model/Carrier/Bliskapaczka.php';

use PHPUnit\Framework\TestCase;
use Bliskapaczka\ApiClient;

class BliskapaczkaTest extends TestCase
{
    protected $shippingCodes = array('DPD', 'RUCH', 'POCZTA', 'INPOST');
    protected $request;

    protected function setUp()
    {

        $this->quoteItem = $this->getMockBuilder(Mage_Sales_Model_Quote_Item::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->getMock();

        $this->request = $this->getMockBuilder(Mage_Shipping_Model_Rate_Request::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'getAllItems',
                                            'getPackageQty',
                                            'getFreeBoxes'
                                        )
                                    )
                                     ->getMock();
        $this->request->method('getAllItems')->willReturn(array($this->quoteItem));

        $this->helper = $this->getMockBuilder(Sendit_Bliskapaczka_Helper_Data::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('getParcelDimensions'))
                                     ->getMock();
        $this->helper->method('getParcelDimensions')->will($this->returnValue(''));
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists('Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka'));
    }

    public function testClassImplementInterface()
    {
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka', 'collectRates'));
        $this->assertTrue(method_exists('Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka', 'getAllowedMethods'));
    }

    public function testGetAllowedMethods()
    {
        $bp = $this->getMockBuilder(Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::class)
            ->setMethods(array('_getPricing'))
            ->getMock();

        $bp->expects($this->once())
            ->method('_getPricing')
            ->willReturn($this->getPricing());

        $allowedShippingMethods = $bp->getAllowedMethods();
        $this->assertTrue(is_array($allowedShippingMethods));
        foreach ($this->shippingCodes as $shippingCode) {
            $this->assertTrue(array_key_exists($shippingCode, $allowedShippingMethods));
        }

    }

    protected function getPricing()
    {
        $dpd = new StdClass();
        $dpd->operatorName = 'DPD';
        $dpd->operatorFullName = 'DPD';

        $ruch = new StdClass();
        $ruch->operatorName = 'RUCH';
        $ruch->operatorFullName = 'Ruch';

        $poczta = new StdClass();
        $poczta->operatorName = 'POCZTA';
        $poczta->operatorFullName ='Poczta Polska';

        $inpost = new StdClass();
        $inpost->operatorName = 'INPOST';
        $inpost->operatorFullName ='Inpost';

        return array(
            $dpd,
            $ruch,
            $poczta,
            $inpost
        );
    }

    protected function getOperator()
    {
        $price = new StdClass();
        $price->gross = 10.0;
        $operator = new StdClass();
        $operator->operatorName = 'DPD';
        $operator->operatorFullName = 'DPD';
        $operator->price = $price;

        return $operator;
    }

    protected static function getMethod($name) {
        $class = new ReflectionClass(Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testFreeShippingWithSingleCartRuleCondition() {
        $ruleConditionCombine = $this->getMockBuilder(Mage_SalesRule_Model_Rule_Condition_Combine::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'validate'
                                        )
                                    )
                                     ->getMock();
        $ruleConditionCombine->method('validate')->with()->will($this->returnValue(true));

        $rule = $this->getMockBuilder(Mage_SalesRule_Model_Resource_Rule::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'getConditionsSerialized',
                                            'setConditionsSerialized',
                                            'getConditions'
                                        )
                                    )
                                    ->getMock();
        $rule->method('getConditionsSerialized')->will($this->returnValue(
            array(
                'conditions' => array()
            )
        ));
        $rule->method('getConditions')->willReturn($ruleConditionCombine);

        $rulesCollection = $this->getMockBuilder(Mage_SalesRule_Model_Resource_Rule_Collection::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('getIterator'))
                                     ->getMock();
        $rulesCollection->method('getIterator')->willReturn(new \ArrayObject(array($rule)));

        $rules = array('single' => $rulesCollection);

        $this->request->method('getPackageQty')->will($this->returnValue(true));
        $this->request->method('getFreeBoxes')->will($this->returnValue(false));

        $shippingPrice = self::getMethod('_shippingPrice');
        $bliskapaczka = new Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka();

        $price = $shippingPrice->invokeArgs($bliskapaczka, array($this->getOperator(), $this->request, $rules, false));

        $this->assertEquals(0.00, $price);
    }

    public function testFreeShippingWithoutAnySingleCartRuleCondition() {
        $ruleConditionCombine = $this->getMockBuilder(Mage_SalesRule_Model_Rule_Condition_Combine::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'validate'
                                        )
                                    )
                                     ->getMock();
        $ruleConditionCombine->method('validate')->with()->will($this->returnValue(false));

        $rule = $this->getMockBuilder(Mage_SalesRule_Model_Resource_Rule::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'getConditionsSerialized',
                                            'setConditionsSerialized',
                                            'getConditions'
                                        )
                                    )
                                    ->getMock();
        $rule->method('getConditionsSerialized')->will($this->returnValue(
            array(
                'conditions' => array()
            )
        ));
        $rule->method('getConditions')->willReturn($ruleConditionCombine);

        $rulesCollection = $this->getMockBuilder(Mage_SalesRule_Model_Resource_Rule_Collection::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('getIterator'))
                                     ->getMock();
        $rulesCollection->method('getIterator')->willReturn(new \ArrayObject(array($rule)));

        $rules = array('single' => $rulesCollection);

        $this->request->method('getPackageQty')->will($this->returnValue(true));
        $this->request->method('getFreeBoxes')->will($this->returnValue(false));

        $shippingPrice = self::getMethod('_shippingPrice');
        $bliskapaczka = new Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka();

        $price = $shippingPrice->invokeArgs($bliskapaczka, array($this->getOperator(), $this->request, $rules, false));

        $this->assertEquals(10, $price);
    }

    public function testFreeShippingWithAggregatedCartRuleCondition() {
        $ruleConditionCombine = $this->getMockBuilder(Mage_SalesRule_Model_Rule_Condition_Combine::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'validate'
                                        )
                                    )
                                     ->getMock();
        $ruleConditionCombine->method('validate')->with()->will($this->returnValue(true));

        $rule = $this->getMockBuilder(Mage_SalesRule_Model_Resource_Rule::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(
                                        array(
                                            'getConditionsSerialized',
                                            'setConditionsSerialized',
                                            'getConditions'
                                        )
                                    )
                                    ->getMock();
        $rule->method('getConditionsSerialized')->will($this->returnValue(
            array(
                'conditions' => array()
            )
        ));
        $rule->method('getConditions')->willReturn($ruleConditionCombine);

        $rulesCollection = $this->getMockBuilder(Mage_SalesRule_Model_Resource_Rule_Collection::class)
                                     ->disableOriginalConstructor()
                                     ->disableOriginalClone()
                                     ->disableArgumentCloning()
                                     ->disallowMockingUnknownTypes()
                                     ->setMethods(array('getIterator'))
                                     ->getMock();
        $rulesCollection->method('getIterator')->willReturn(new \ArrayObject(array($rule)));

        $rules = array('aggregated' => $rulesCollection);

        $this->request->method('getPackageQty')->will($this->returnValue(true));
        $this->request->method('getFreeBoxes')->will($this->returnValue(false));

        $shippingPrice = self::getMethod('_shippingPrice');
        $bliskapaczka = new Sendit_Bliskapaczka_Model_Carrier_Bliskapaczka();

        $price = $shippingPrice->invokeArgs($bliskapaczka, array($this->getOperator(), $this->request, $rules, false));

        $this->assertEquals(0.00, $price);
    }
}
