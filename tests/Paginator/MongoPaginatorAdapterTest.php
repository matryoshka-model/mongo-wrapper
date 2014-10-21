<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Paginator;

use Matryoshka\Model\Wrapper\Mongo\Paginator\MongoPaginatorAdapter;

/**
 * Class MongoPaginatorAdapterTest
 */
class MongoPaginatorAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;

    protected $mongoCursorMock;

    public function setUp()
    {
        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
            ->disableOriginalConstructor()
            ->setMethods(['limit', 'skip', 'count', 'rewind'])
            ->getMock();


        $mongoCursorMock->expects($this->any())
            ->method('limit')
            ->will($this->returnSelf());

        $mongoCursorMock->expects($this->any())
            ->method('skip')
            ->will($this->returnSelf());

        $mongoCursorMock->expects($this->any())
                        ->method('count')
                        ->will($this->returnValue(10));

        $this->mongoCursorMock = $mongoCursorMock;

        $this->adapter = new MongoPaginatorAdapter($mongoCursorMock);
    }

    public function testCount()
    {
        $this->assertCount(10, $this->adapter);
    }

    public function testGetItems()
    {
        $offset = 11;
        $itemCountPerPage = 10;
        $mongoCursorMock = $this->mongoCursorMock;

        $mongoCursorMock->expects($this->any())
            ->method('skip')
            ->with($this->equalTo($offset))
            ->will($this->returnSelf());

        $mongoCursorMock->expects($this->any())
            ->method('limit')
            ->with($this->equalTo($itemCountPerPage))
            ->will($this->returnSelf());

        $resultSet = $this->adapter->getItems($offset, $itemCountPerPage);

        $this->assertInstanceOf('Matryoshka\Model\ResultSet\HydratingResultSetInterface', $resultSet);
    }
}
