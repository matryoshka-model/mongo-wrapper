<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Model\Wrapper\Mongo\Criteria\Isolated;

use Matryoshka\Model\Model;
use Matryoshka\Model\ResultSet\ArrayObjectResultSet;
use Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated\ActiveRecordCriteria;
use Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated\DocumentStore;
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

    protected static $sharedDataGateway;

    public static function setUpBeforeClass()
    {
        self::disableStrictErrors();
        self::$sharedDataGateway = new MongoCollectionMockProxy();
        self::restoreErrorReportingLevel();
    }

    /** @var \PHPUnit_Framework_MockObject_MockObject $mongoCollectionMock */
    protected $mongoCollectionMock;

    /**
     * @var ActiveRecordCriteria
     */
    protected $criteria;

    protected $model;


    public function setUp()
    {
        $mongoCollectionMock = $this->getMockBuilder('\MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'find', 'remove', 'insert', 'update', 'getName'])
            ->getMock();

        $mongoCollectionMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('testCollection'));

        self::$sharedDataGateway->__MongoCollectionMockProxy__setMock($mongoCollectionMock);

        $this->mongoCollectionMock = $mongoCollectionMock;
        $this->criteria = new ActiveRecordCriteria();
        $rs = new ArrayObjectResultSet();
        $model = new Model(self::$sharedDataGateway, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);

        $this->model = $model;
    }

    /**
     * @param $id
     * @return mixed
     */
    protected function getDocumentFromCache($id)
    {
        return DocumentStore::getSharedInstance()->get(self::$sharedDataGateway, $id);
    }

    /**
     * @param $id
     * @param array $document
     */
    protected function assertHasDocumentCache($id, array $document)
    {
        $this->assertTrue(
            DocumentStore::getSharedInstance()->has(self::$sharedDataGateway, $id)
        );
        $this->assertSame(
            $document,
            DocumentStore::getSharedInstance()->get(self::$sharedDataGateway, $id)
        );
    }


    public function testApply()
    {
        $testId = 'test-apply-id';
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


        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('find')
            ->with($this->equalTo(['_id' => $testId]))
            ->will($this->returnValue($mongoCursorMock));


        $criteria = $this->criteria;
        $criteria->setId($testId);

        $res = $criteria->apply($this->model);
        $this->assertSame([$expectedResult], $res);
        $this->assertHasDocumentCache($testId, $expectedResult);
    }


    /**
     * @depends testApply
     */
    public function testApplyWrite()
    {
        $model    = $this->model;
        $criteria = $this->criteria;

        // Test insert
        $testId = 'test-apply-write-id';
        $expectedResult = ['_id' => $testId, 'baz' => 'bar'];

        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('insert')
            ->with($this->equalTo($expectedResult), $this->equalTo($criteria->getSaveOptions()))
            ->will($this->returnValue(['ok' => true, 'n' => 0])); // MongoDB returns 0 on insert operation

        $criteria->setId($testId);

        $res = $criteria->applyWrite($model, $expectedResult);
        $this->assertEquals(1, $res);

        // Test update
        $currentDataState = $expectedResult;
        $expectedResult['baz'] = 'foo';
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('update')
            ->with(
                $this->equalTo($currentDataState),
                $this->equalTo($expectedResult),
                $this->equalTo(['multi' => false, 'upsert' => false] + $criteria->getMongoOptions())
            )
            ->will($this->returnValue(['ok' => true, 'n' => 1, 'updatedExisting' => true]));

        $res = $criteria->applyWrite($model, $expectedResult);
        $this->assertEquals(1, $res);
        $this->assertHasDocumentCache($testId, $expectedResult);
    }

    /**
     * @depends testApplyWrite
     */
    public function testApplyWriteWithoutId()
    {
        $model    = $this->model;
        $criteria = $this->criteria;
        $testData = ['test' => 'test'];

        //Test insert
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('insert')
            ->with($this->equalTo($testData), $this->equalTo($criteria->getMongoOptions()))
            ->will($this->returnValue(['ok' => true, 'n' => 0])); // MongoDB returns 0 on insert operation


        $this->assertEquals(1, $criteria->applyWrite($model, $testData));
        $this->assertInstanceOf('\MongoId', $testData['_id']);
        $testId = (string) $testData['_id'];

        $this->assertHasDocumentCache($testId, $testData);
    }

    /**
     * @depends testApplyWriteWithoutId
     */
    public function testApplyDelete()
    {
        $model = $this->model;
        $criteria = $this->criteria;
        $testId = 'test-apply-id';
        $testData = $this->getDocumentFromCache($testId);

        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('remove')
            ->with($this->equalTo($testData))
            ->will($this->returnValue(['ok' => true, 'n' => 1]));

        $criteria->setId($testId);
        $this->assertEquals(1, $criteria->applyDelete($model));

        $this->setExpectedException('\Matryoshka\Model\Exception\RuntimeException');
        $criteria->applyDelete($model);
    }

    /**
     * @expectedException \Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException
     */
    public function testApplyDeleteShouldThrowExceptionWhenDocumentModified()
    {
        $criteria = $this->criteria;
        $testId = 'test-apply-write-id';
        $testData = $this->getDocumentFromCache($testId);

        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('remove')
            ->with($this->equalTo($testData))
            ->will($this->returnValue(['ok' => true, 'n' => 0]));

        $criteria->setId($testId);
        $this->assertEquals(1, $criteria->applyDelete($this->model));
    }

    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testApplyDeleteWithNoFindBefore()
    {
        $criteria = $this->criteria;
        $testId = 1;

        $criteria->setId($testId);
        $criteria->applyDelete($this->model);
    }


    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testApplyDeleteWithoutId()
    {
        $this->criteria->applyDelete($this->model);
    }
}
