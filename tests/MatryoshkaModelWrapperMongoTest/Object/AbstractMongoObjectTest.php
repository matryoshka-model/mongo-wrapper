<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Object;

use MatryoshkaModelWrapperMongoTest\Object\TestAsset\ObjectPropertyMongoObject;

class AbstractMongoObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MongoObject
     */
    protected $mongoObject;

    public function setUp()
    {
        $this->mongoObject = new ObjectPropertyMongoObject();
    }

    public function testGetNotPresetHydrator()
    {
        $this->assertInstanceOf('Zend\Stdlib\Hydrator\ObjectProperty', $this->mongoObject->getHydrator());
    }

    public function testGetNotPresetInputFilter()
    {
        $this->assertInstanceOf('Zend\InputFilter\InputFilter', $this->mongoObject->getInputFilter());
    }

    public function testGetNotPresetId()
    {
        $this->assertNull($this->mongoObject->getId());
    }

    public function testSetId()
    {
        $mongoObject = $this->mongoObject->setId("test");
        $this->assertSame($mongoObject, $this->mongoObject);
    }

    public function testObjectExistsInDatabase()
    {
        $this->assertFalse($this->mongoObject->objectExistsInDatabase());
    }

    /**
     * @expectedException Exception
     * @testdox Set exception
     */
    public function testException__set()
    {
        $this->mongoObject->test = 4;
    }

    /**
     * @expectedException Exception
     * @testdox Get exception
     */
    public function testException__get()
    {
        $test =  $this->mongoObject->test;
    }

    /**
     * @expectedException Exception
     * @testdox Set exception
     */
    public function testException__unset()
    {
        unset($this->mongoObject->test);
    }

    /**
     * @testdox Isset exception
     */
    public function testException__isset()
    {
        $this->assertFalse(isset($this->mongoObject->test));
    }
}
