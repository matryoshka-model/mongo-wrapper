<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Object;

/**
 * Class ObjectPropertyTraitTest
 */
class ObjectPropertyTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectPropertyTrait
     */
    protected $objectPropertyTrait;

    public function setUp()
    {
        $this->objectPropertyTrait = $this->getMockForTrait('Matryoshka\Model\Wrapper\Mongo\Object\ObjectPropertyTrait');
    }

    public function testGetHydrator()
    {
        $this->assertInstanceOf('Zend\Stdlib\Hydrator\ObjectProperty', $this->objectPropertyTrait->getHydrator());
    }

    public function testGetSetId()
    {
        //Test Id not present
        $this->assertNull($this->objectPropertyTrait->getId());

        $this->assertSame($this->objectPropertyTrait, $this->objectPropertyTrait->setId('test'));

        $this->assertSame('test', $this->objectPropertyTrait->getId());
    }

    /**
     * @expectedException \Exception
     * @testdox Set exception
     */
    public function testException__set()
    {
        $this->objectPropertyTrait->test = 4;
    }

    /**
     * @expectedException \Exception
     * @testdox Get exception
     */
    public function testException__get()
    {
        $test =  $this->objectPropertyTrait->test;
    }

    /**
     * @expectedException \Exception
     * @testdox Set exception
     */
    public function testException__unset()
    {
        unset($this->objectPropertyTrait->test);
    }
}
