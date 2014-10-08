<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\ResultSet;

use Matryoshka\Model\Wrapper\Mongo\ResultSet\HydratingResultSet;

/**
 * Class HydratingResultSetTest
 */
class HydratingResultSetTest extends \PHPUnit_Framework_TestCase
{
    public function testCount()
    {
        /**
         * @see http://php.net/manual/en/mongocursor.count.php
         */
        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCursorMock->expects($this->atLeastOnce())
            ->method('count')
            ->with($this->equalTo(true))
            ->will($this->returnValue(11));

        $resultSet = new HydratingResultSet();
        $resultSet->initialize($mongoCursorMock);

        $this->assertEquals(11, $resultSet->count());
    }
}
