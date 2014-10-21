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
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\MongoCollectionSubject;
use Zend\Stdlib\Hydrator\ObjectProperty;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\FindAllCriteria;

/**
 * Class FindAllCriteriaTest
 */
class FindAllCriteriaTest extends \PHPUnit_Framework_TestCase
{
    protected $modelInterfaceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject $mongoCollectionMock */
    protected $mongoCollectionMock;

    public function setUp()
    {
        $mongoCollectionMock = $this->getMockBuilder('\MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(['find'])
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

    public function testDefaultSelectionCriteria()
    {
        $criteria = new FindAllCriteria();
        $this->assertEquals([], $criteria->getSelectionCriteria());
    }

    public function testApply()
    {
        $criteria = new FindAllCriteria();

        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
        ->disableOriginalConstructor()
        ->getMock();

        $mongoCursorMock->expects($this->at(0))
        ->method('limit')
        ->with($this->equalTo($criteria->getLimit()))
        ->will($this->returnValue($mongoCursorMock));

        $mongoCursorMock->expects($this->at(1))
        ->method('skip')
        ->with($this->equalTo($criteria->getOffset()))
        ->will($this->returnValue($mongoCursorMock));

        $this->mongoCollectionMock->expects($this->at(0))
        ->method('find')
        ->with($this->equalTo($criteria->getSelectionCriteria()))
        ->will($this->returnValue($mongoCursorMock));

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);

        $res = $criteria->apply($model);

        $this->assertSame($mongoCursorMock, $res);
    }

    public function testApplyWithSelectionAndSort()
    {
        $selectionParams = ['baz' => 'bar'];
        $sortParams      = ['foo' => -1];

        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCursorMock->expects($this->at(0))
            ->method('sort')
            ->with($sortParams)
            ->will($this->returnValue($mongoCursorMock));

        $mongoCursorMock->expects($this->at(1))
            ->method('limit')
            ->with($this->equalTo(1))
            ->will($this->returnValue($mongoCursorMock));

        $mongoCursorMock->expects($this->at(2))
            ->method('skip')
            ->with($this->equalTo(1))
            ->will($this->returnValue($mongoCursorMock));

        $this->mongoCollectionMock->expects($this->at(0))
            ->method('find')
            ->with($this->equalTo($selectionParams))
            ->will($this->returnValue($mongoCursorMock));

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);

        $criteria = new FindAllCriteria();
        $criteria->setSort($sortParams);
        $criteria->setSelectionCriteria($selectionParams);
        $criteria->limit(1)->offset(1);

        $res = $criteria->apply($model);

        $this->assertSame($mongoCursorMock, $res);
    }
    /**
     * @expectedException \Matryoshka\Model\Exception\RuntimeException
     */
    public function testExtractValue()
    {
        $key = 'foo';
        $value = 'baz';
        $obj = new \stdClass();
        $extratedValue = 'bar';
        $criteria = new FindAllCriteria();

        $reflection = new \ReflectionClass($criteria);
        $reflMethod = $reflection->getMethod('extractValue');
        $reflMethod->setAccessible(true);

        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);

        $hydratorMock = $this->getMock('\Zend\Stdlib\Hydrator\ObjectProperty', ['extractValue']);

        $hydratorMock->expects($this->atMost(1))
                     ->method('extractValue')
                     ->with($this->equalTo($key), $this->equalTo($value), $this->identicalTo($obj))
                     ->willReturn($extratedValue);

        $model->setHydrator($hydratorMock);
        $this->assertEquals($extratedValue, $reflMethod->invoke($criteria, $model, $key, $value, $obj));


        //Test invalid hydrator
        $rs = new ArrayObjectResultSet();
        $model = new Model($this->mongoCollectionMock, $rs);
        $hyd = new BadHydrator();
        $model->setHydrator($hyd);

        $reflMethod->invoke($criteria, $model, 'foo', 'bar');
    }

    public function testGetPaginatorAdapter()
    {
        $criteria = new FindAllCriteria();

        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
        ->disableOriginalConstructor()
        ->getMock();

        $mongoCursorMock->expects($this->any())
        ->method('limit')
        ->will($this->returnValue($mongoCursorMock));

        $mongoCursorMock->expects($this->any())
        ->method('skip')
        ->will($this->returnValue($mongoCursorMock));

        $this->mongoCollectionMock->expects($this->any())
            ->method('find')
            ->will($this->returnValue($mongoCursorMock));

        $this->assertInstanceOf('Matryoshka\Model\Wrapper\Mongo\Paginator\MongoPaginatorAdapter', $criteria->getPaginatorAdapter($this->modelInterfaceMock));
    }

}
