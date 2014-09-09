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
use Zend\Stdlib\Hydrator\ObjectProperty;

/**
 * Class ActiveRecordCriteriaTest
 *
 * @author Lorenzo Fontana <fontanalorenzo@me.com>
 */
class ActiveRecordCriteriaTest extends \PHPUnit_Framework_TestCase
{

    protected $modelInterfaceMock;

    protected $mongoCollectionMock;

    public function setUp()
    {

        $mongoCollectionMock = $this->getMockBuilder('\MongoCollection')
            ->disableOriginalConstructor()
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

        $this->assertTrue($res);
    }
}
