<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Model\Wrapper\Mongo\Criteria\ActiveRecord;

use Matryoshka\Model\Model;
use Matryoshka\Model\ResultSet\ArrayObjectResultSet;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\BadHydrator;
use MatryoshkaModelWrapperMongoTest\TestAsset\MongoCollectionMockProxy;
use Zend\Stdlib\Hydrator\ObjectProperty;

/**
 * Class ActiveRecordCriteriaTest
 */
class ActiveRecordCriteriaTest extends \PHPUnit_Framework_TestCase
{
    protected static $oldErrorLevel;

    protected static function disableStrictErrors()
    {
        self::$oldErrorLevel = error_reporting();
        error_reporting(self::$oldErrorLevel & ~E_STRICT);
    }

    protected static function restoreErrorReportingLevel()
    {
        error_reporting(self::$oldErrorLevel);
    }

    /**
     * @var Model
     */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject $mongoCollectionMock */
    protected $mongoCollectionMock;

    public function setUp()
    {
        $mongoCollectionMock = $this->getMockBuilder('\MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'find', 'remove'])
            ->getMock();

        $this->mongoCollectionMock = $mongoCollectionMock;

        self::disableStrictErrors();
        $mockProxy = new MongoCollectionMockProxy();
        self::restoreErrorReportingLevel();
        $mockProxy->__MongoCollectionMockProxy__setMock($mongoCollectionMock);

        $rs = new ArrayObjectResultSet();
        $model = new Model($mockProxy, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);

        $this->model = $model;
    }

    public function testApply()
    {
        $testId = 1;
        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCursorMock->expects($this->at(0))
            ->method('limit')
            ->with($this->equalTo(1))
            ->will($this->returnValue($mongoCursorMock));

        $this->mongoCollectionMock->expects($this->at(0))
            ->method('find')
            ->with($this->equalTo(['_id' => $testId]))
            ->will($this->returnValue($mongoCursorMock));


        $ar = new ActiveRecordCriteria();
        $ar->setId($testId);
        $res = $ar->apply($this->model);

        $this->assertEquals($mongoCursorMock, $res);
    }

    public function testApplyWrite()
    {
        $ar = new ActiveRecordCriteria();
        $testId = 1;
        $testData = ['_id' => $testId];

        $this->mongoCollectionMock->expects($this->at(0))
            ->method('save')
            ->with($this->equalTo($testData), $this->equalTo($ar->getSaveOptions()));


        $ar->setId($testId);
        $res = $ar->applyWrite($this->model, $testData);

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

        $this->model->setHydrator(new BadHydrator());

        $ar->setId($testId);
        $ar->applyWrite($this->model, $testData);
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

        $ar->applyWrite($this->model, $testData);
        $this->assertInstanceOf('\MongoId', $testData['_id']);
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


        $ar->setId($testId);
        $res = $ar->applyDelete($this->model);

        $this->assertNull($res);
    }

    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testApplyDeleteWithBadHydrator()
    {
        $ar = new ActiveRecordCriteria();
        $testId = 1;

        $this->model->setHydrator(new BadHydrator());
        $ar->setId($testId);
        $ar->applyDelete($this->model);
    }

    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testApplyDeleteWithoutId()
    {
        $ar = new ActiveRecordCriteria();

        $ar->applyDelete($this->model);
    }
}
