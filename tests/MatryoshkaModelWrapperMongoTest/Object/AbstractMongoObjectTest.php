<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Object;

use MatryoshkaModelWrapperMongoTest\Object\TestAsset\MongoObject;

/**
 * Class AbstractMongoObjectTest
 */
class AbstractMongoObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MongoObject
     */
    protected $mongoObject;

    public function setUp()
    {
        $this->mongoObject = new MongoObject();
    }

    public function testGetSetModel()
    {
        $abstractModelMock  = $this->getMockForAbstractClass('Matryoshka\Model\AbstractModel');

        $this->assertInstanceOf('\MatryoshkaModelWrapperMongoTest\Object\TestAsset\MongoObject', $this->mongoObject->setModel($abstractModelMock));

        $this->assertSame($abstractModelMock, $this->mongoObject->getModel());

        $modelInterfaceMock = $this->getMockForAbstractClass('Matryoshka\Model\ModelInterface');
        $this->setExpectedException('Matryoshka\Model\Exception\InvalidArgumentException');
        $this->mongoObject->setModel($modelInterfaceMock);

    }


    public function testGetNotPresetInputFilter()
    {
        $this->assertInstanceOf('Zend\InputFilter\InputFilter', $this->mongoObject->getInputFilter());
    }

    public function testObjectExistsInDatabase()
    {
        $this->assertFalse($this->mongoObject->objectExistsInDatabase());
    }

    public function testSave()
    {
        $abstractModelMock  = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
                                    ->disableOriginalConstructor()
                                    ->setMethods(['save'])
                                    ->getMock();
        $result = null;

        $abstractModelMock->expects($this->at(0))
                           ->method('save')
                           ->with($this->isInstanceOf('Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecordCriteria'), $this->identicalTo($this->mongoObject))
                           ->will($this->returnValue($result));

        $this->mongoObject->setModel($abstractModelMock);

        $this->assertSame($result, $this->mongoObject->save());
        $this->assertTrue($this->mongoObject->objectExistsInDatabase());
    }


    public function testDelete()
    {
        $abstractModelMock  = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
                            ->disableOriginalConstructor()
                            ->setMethods(['save', 'delete'])
                            ->getMock();
        $result = null;

        $abstractModelMock->expects($this->at(0))
                        ->method('save')
                        ->with($this->isInstanceOf('Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecordCriteria'), $this->identicalTo($this->mongoObject))
                        ->will($this->returnValue($result));


        $abstractModelMock->expects($this->at(1))
                        ->method('delete')
                        ->with($this->isInstanceOf('Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecordCriteria'))
                        ->will($this->returnValue($result));

        $this->mongoObject->setModel($abstractModelMock);
        $this->mongoObject->save();

        $this->assertSame($result, $this->mongoObject->delete());
        $this->assertFalse($this->mongoObject->objectExistsInDatabase());
    }

    public function testDeleteShouldThrowExceptionWhenObjectDoesntExistInDatabase()
    {
        $this->setExpectedException('Matryoshka\Model\Exception\RuntimeException');
        $this->mongoObject->delete();
    }

}
