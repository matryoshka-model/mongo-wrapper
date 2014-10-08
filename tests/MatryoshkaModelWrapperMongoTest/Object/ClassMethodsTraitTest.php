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
 * Class ClassMethodsTraitTest
 */
class ClassMethodsTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMethodsTrait
     */
    protected $classMethodsTrait;

    public function setUp()
    {
        $this->classMethodsTrait = $this->getMockForTrait('Matryoshka\Model\Wrapper\Mongo\Object\ClassMethodsTrait');
    }

    public function testGetHydrator()
    {
        $this->assertInstanceOf('Zend\Stdlib\Hydrator\ClassMethods', $this->classMethodsTrait->getHydrator());
    }

    public function testGetSetId()
    {
        //Test Id not present
        $this->assertNull($this->classMethodsTrait->getId());

        $this->assertSame($this->classMethodsTrait, $this->classMethodsTrait->setId('test'));

        $this->assertSame('test', $this->classMethodsTrait->getId());
    }

    /**
     * @expectedException \Exception
     * @testdox Set exception
     */
    public function testException__set()
    {
        $this->classMethodsTrait->test = 4;
    }

    /**
     * @expectedException \Exception
     * @testdox Get exception
     */
    public function testException__get()
    {
        $test =  $this->classMethodsTrait->test;
    }

    /**
     * @expectedException \Exception
     * @testdox Set exception
     */
    public function testException__unset()
    {
        unset($this->classMethodsTrait->test);
    }
}
