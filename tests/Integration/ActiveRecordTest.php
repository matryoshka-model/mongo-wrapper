<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Integration;

use Matryoshka\Model\ModelStubInterface;
use MatryoshkaModelWrapperMongoTest\Object\TestAsset\MongoObject;

/**
 * Class ActiveRecordTest
 * @group integration
 */
class ActiveRecordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MongoObject
     */
    protected $mongoObject;

    public function setUp()
    {
        $this->mongoObject = new MongoObject();
    }

    public function testSave()
    {
        $abstractModelMock = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
                                    ->disableOriginalConstructor()
                                    ->setMethods(['save'])
                                    ->getMock();
        $result = null;
        $abstractModelMock->expects($this->atLeastOnce())
                           ->method('save')
                           ->with(
                               $this->isInstanceOf('Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria'),
                               $this->identicalTo($this->mongoObject)
                           )
                           ->will($this->returnValue($result));

        /** @var $abstractModelMock ModelStubInterface */
        $this->mongoObject->setModel($abstractModelMock);

        $this->assertSame($result, $this->mongoObject->save());
    }


    public function testDelete()
    {
        $abstractModelMock  = $this->getMockBuilder('Matryoshka\Model\AbstractModel')
                            ->disableOriginalConstructor()
                            ->setMethods(['save', 'delete'])
                            ->getMock();
        $result = null;
        $abstractModelMock->expects($this->atLeastOnce())
                        ->method('delete')
                        ->with($this->isInstanceOf('Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria'))
                        ->will($this->returnValue($result));

        /** @var $abstractModelMock ModelStubInterface */
        $this->mongoObject->setModel($abstractModelMock);
        $this->mongoObject->setId('id');

        $this->assertSame($result, $this->mongoObject->delete());
    }

    public function testDeleteShouldThrowExceptionWhenObjectDoesntExistInDatabase()
    {
        $this->setExpectedException('Matryoshka\Model\Exception\RuntimeException');
        $this->mongoObject->delete();
    }
}
