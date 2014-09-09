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

    public function setUp()
    {
        $mongoCursorMock = $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $modelMock = $this->getMockBuilder('\Matryoshka\Model\Model')
            ->disableOriginalConstructor()
            ->getMock();

        $modelInterfaceMock = $this->getMockBuilder('\Matryoshka\Model\ModelInterface')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getDataGateway',
                    'getInputFilter',
                    'getHydrator',
                    'getObjectPrototype',
                    'getResultSetPrototype',
                    'find'
                ]
            )
            ->getMock();

        $modelInterfaceMock->expects($this->any())
            ->method('find')
            ->will($this->returnValue($mongoCursorMock));

        $modelInterfaceMock->expects($this->any())
            ->method('getDataGateway')
            ->will($this->returnValue($modelMock));

        $this->modelInterfaceMock = $modelInterfaceMock;
    }

    public function testApply()
    {
        $rs = new ArrayObjectResultSet();
        $model = new Model($this->modelInterfaceMock, $rs);
        $hyd = new ObjectProperty();
        $model->setHydrator($hyd);
        $ar = new ActiveRecordCriteria();
        $ar->setId(1);
        $res = $ar->apply($model);
        var_dump($res);
    }
}
