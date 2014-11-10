<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Model\Wrapper\Mongo\Criteria\ActiveRecord;

use Matryoshka\Model\Model;
use Matryoshka\Model\ResultSet\ArrayObjectResultSet;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\BadHydrator;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\MongoCollectionSubject;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\IsolatedCriteria;

/**
 * Class UpdateIfCurrentTest
 */
class IsolatedCriteriaTest extends \PHPUnit_Framework_TestCase
{
    protected $modelInterfaceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject $mongoCollectionMock */
    protected $mongoCollectionMock;

    /**
     * @var IsolatedCriteria
     */
    protected $criteria;

    public function setUp()
    {
        $mongoCollectionMock = $this->getMockBuilder('\MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'find', 'remove', 'insert', 'update'])
            ->getMock();

        $this->mongoCollectionMock = $mongoCollectionMock;

        $modelInterfaceMock = $this->getMockBuilder('\Matryoshka\Model\ModelInterface')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getDataGateway',
                    'getInputFilter',
                    'getHydrator',
                    'getObjectPrototype',
                    'getResultSetPrototype'
                ]
            )
            ->getMock();

        $modelInterfaceMock->expects($this->any())
            ->method('getDataGateway')
            ->will($this->returnValue($mongoCollectionMock));

        $this->modelInterfaceMock = $modelInterfaceMock;
        $this->criteria = new IsolatedCriteria();
    }

    public function testApply()
    {
        $testId = 1;
        $expectedResult = ['_id' => $testId, 'foo' => 'bar'];

        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCursorMock->expects($this->at(0))
            ->method('limit')
            ->with($this->equalTo(1))
            ->will($this->returnValue($mongoCursorMock));

        //Emulate foreach behavior
        $mongoCursorMock->expects($this->at(1))
            ->method('rewind');

        $mongoCursorMock->expects($this->at(2))
            ->method('valid')
            ->will($this->returnValue(true));

        $mongoCursorMock->expects($this->at(3))
            ->method('current')
            ->will($this->returnValue($expectedResult));

        $mongoCursorMock->expects($this->at(4))
            ->method('key')
            ->will($this->returnValue(0));

        $mongoCursorMock->expects($this->at(5))
            ->method('next')
            ->will($this->returnValue(false));


        $this->mongoCollectionMock->expects($this->at(0))
            ->method('find')
            ->with($this->equalTo(['_id' => $testId]))
            ->will($this->returnValue($mongoCursorMock));

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $criteria = $this->criteria;
        $criteria->setId($testId);
        $res = $criteria->apply($model);

        $this->assertSame([$expectedResult], $res);
        $this->assertAttributeEquals([$testId => $expectedResult], 'initialStateCache', $criteria);
    }

    public function testApplyWrite()
    {
        $criteria = $this->criteria;
        $testId = 1;
        $expectedResult = ['_id' => $testId, 'baz' => 'bar'];

        $this->mongoCollectionMock->expects($this->at(0))
            ->method('insert')
            ->with($this->equalTo($expectedResult), $this->equalTo($criteria->getSaveOptions()))
            ->will($this->returnValue(['ok' => true, 'n' => 0])); // MongoDB returns 0 on insert operation

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $criteria->setId($testId);
        $res = $criteria->applyWrite($model, $expectedResult);

        $this->assertEquals(1, $res);
        $this->assertAttributeEquals([$testId => $expectedResult], 'initialStateCache', $criteria);

    }

    public function testApplyWriteWithoutId()
    {
        $criteria = $this->criteria;
        $testId = null;
        $testData = ['_id' => $testId, 'test' => 'test'];
        $testUnsetData = $testData;
        unset($testUnsetData['_id']);

        //Test insert
        $this->mongoCollectionMock->expects($this->at(0))
            ->method('insert')
            ->with($this->equalTo($testUnsetData), $this->equalTo($criteria->getSaveOptions()))
            ->will($this->returnValue(['ok' => true, 'n' => 0])); // MongoDB returns 0 on insert operation

        $mock = new MongoCollectionSubject($this->mongoCollectionMock);

        $rs = new ArrayObjectResultSet();
        $model = new Model($mock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);

        $this->assertEquals(1, $criteria->applyWrite($model, $testData));
        $this->assertInstanceOf('\MongoId', $testData['_id']);
        $this->assertAttributeEquals([(string)$testData['_id'] => $testData], 'initialStateCache', $criteria);
        $criteria->setId($testData['_id']);


        //Test update
        $currentDataState = $testData;
        $testData['baz'] = 'bar';

        $this->mongoCollectionMock->expects($this->at(0))
            ->method('update')
            ->with(
                $this->equalTo($currentDataState),
                $this->equalTo(['$set' => $testData]),
                $this->equalTo(array_merge($criteria->getSaveOptions(), ['multi' => false, 'upsert' => false]))
            )
            ->will($this->returnValue(['ok' => true, 'n' => 1, 'updatedExisting' => true]));


        $this->assertEquals(1, $criteria->applyWrite($model, $testData));

        //Test exception
        $currentDataState = $testData;
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('update')
            ->with(
                $this->equalTo($currentDataState),
                $this->equalTo(['$set' => $testData]),
                $this->equalTo(array_merge($criteria->getSaveOptions(), ['multi' => false, 'upsert' => false]))
            )
            ->will($this->returnValue(['ok' => true, 'n' => 0, 'updatedExisting' => false])); //simulate no update


        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $criteria->applyWrite($model, $testData);

    }

    public function testApplyDelete()
    {
        $this->testApply(); // simulate document read
        $criteria = $this->criteria;
        $testId = 1;
        $testData = ['_id' => $testId, 'foo' => 'bar'];

        $this->mongoCollectionMock->expects($this->at(0))
            ->method('remove')
            ->with($this->equalTo($testData))
            ->will($this->returnValue(['ok' => true, 'n' => 1]));

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $criteria->setId($testId);
        $this->assertEquals(1, $criteria->applyDelete($model));


        $this->testApply(); // simulate document read
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('remove')
            ->with($this->equalTo($testData))
            ->will($this->returnValue(['ok' => true, 'n' => 0]));


        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $criteria->applyDelete($model);
    }

    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testApplyDeleteWithNoFindBefore()
    {
        $criteria = $this->criteria;
        $testId = 1;
        $testData = ['_id' => $testId, 'foo' => 'bar'];

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $criteria->setId($testId);
        $criteria->applyDelete($model);
    }


    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testApplyDeleteWithoutId()
    {
        $ar = $this->criteria;
        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $ar->applyDelete($model);
    }


}
