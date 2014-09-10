<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Model\Wrapper\Mongo\Criteria;

use Matryoshka\Model\Model;
use Matryoshka\Model\ResultSet\ArrayObjectResultSet;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecordCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\BadHydrator;
use Zend\Stdlib\Hydrator\ObjectProperty;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\MongoCollectionSubject;

/**
 * Class ActiveRecordCriteriaTest
 */
class ActiveRecordCriteriaTest extends \PHPUnit_Framework_TestCase
{

    protected $modelInterfaceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject $mongoCollectionMock */
    protected $mongoCollectionMock;

    public function setUp()
    {
        $mongoCollectionMock = $this->getMockBuilder('\MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'find', 'remove'])
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
    }

    public function testApply()
    {
        $testId = 1;
        $testReturn = ['foo', 'bar'];
        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCursorMock->expects($this->at(0))
            ->method('limit')
            ->with($this->equalTo(1))
            ->will($this->returnValue($testReturn));

        $this->mongoCollectionMock->expects($this->at(0))
            ->method('find')
            ->with($this->equalTo(['_id' => $testId]))
            ->will($this->returnValue($mongoCursorMock));

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $ar = new ActiveRecordCriteria();
        $ar->setId($testId);
        $res = $ar->apply($model);

        $this->assertEquals($testReturn, $res);
    }


    public function testApplyWrite()
    {
        $ar = new ActiveRecordCriteria();
        $testId = 1;
        $testData = ['_id' => $testId];

        $this->mongoCollectionMock->expects($this->at(0))
            ->method('save')
            ->with($this->equalTo($testData), $this->equalTo($ar->getSaveOptions()));

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $ar->setId($testId);
        $res = $ar->applyWrite($model, $testData);

        $this->assertNull($res);
    }

    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testApplyWriteWithBadHydrator()
    {
        $ar = new ActiveRecordCriteria();
        $testId = 1;
        $testData = ['_id' => $testId];
        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new BadHydrator();
        $model->setHydrator($hyd);
        $ar->setId($testId);
        $ar->applyWrite($model, $testData);
    }

   public function testApplyWriteWithoutId()
   {
       $ar = new ActiveRecordCriteria();
       $testId = null;
       $testData = ['_id' => $testId];
       $testUnsetData = [];

       $this->mongoCollectionMock->expects($this->at(0))
           ->method('save')
           ->with($this->equalTo($testUnsetData), $this->equalTo($ar->getSaveOptions()));

       $mock = new MongoCollectionSubject($this->mongoCollectionMock);

       $rs = new ArrayObjectResultSet();
       $model = new Model($mock, $rs);
       $hyd = new ObjectProperty();
       $model->setHydrator($hyd);

       $res = $ar->applyWrite($model, $testData);

       $this->assertInstanceOf('\MongoId', $ar->getId());
   }

    public function testSaveOptions()
    {
        $saveOptions = ['foo', 'bar'];
        $ar = new ActiveRecordCriteria();
        $ar->setSaveOptions($saveOptions);

        $this->assertEquals($saveOptions, $ar->getSaveOptions());
    }

    public function testApplyDelete()
    {
        $ar = new ActiveRecordCriteria();
        $testId = 1;
        $testData = ['_id' => $testId];

        $this->mongoCollectionMock->expects($this->at(0))
            ->method('remove')
            ->with($this->equalTo($testData));

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $ar->setId($testId);
        $res = $ar->applyDelete($model);

        $this->assertNull($res);
    }

    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testApplyDeleteWithBadHydrator()
    {
        $ar = new ActiveRecordCriteria();
        $testId = 1;
        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new BadHydrator();
        $model->setHydrator($hyd);
        $ar->setId($testId);
        $ar->applyDelete($model);
    }


    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testApplyDeleteWithoutId()
    {
        $ar = new ActiveRecordCriteria();
        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $ar->applyDelete($model);
    }

    public function testHandleResult()
    {
        $activeRecordCriteria = new ActiveRecordCriteria();

        $reflection = new \ReflectionClass($activeRecordCriteria);
        $reflMethod = $reflection->getMethod('handleResult');
        $reflMethod->setAccessible(true);

        $result = $reflMethod->invoke($activeRecordCriteria, null);
        $this->assertNull($result);

        $result = $reflMethod->invoke($activeRecordCriteria, true);
        $this->assertNull($result);

        $result = $reflMethod->invoke($activeRecordCriteria, []);
        $this->assertNull($result);

        $result = $reflMethod->invoke($activeRecordCriteria, ['ok' => 1, 'n' => 1]);
        $this->assertEquals(1, $result);

        $this->setExpectedException('Matryoshka\Model\Wrapper\Mongo\Criteria\Exception\MongoResultException');
        $reflMethod->invoke($activeRecordCriteria, ['err' => 1, 'errmsg' => 'error', 'code' => 100]);
    }
}
