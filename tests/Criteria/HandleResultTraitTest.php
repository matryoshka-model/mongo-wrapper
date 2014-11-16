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
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\BadHydrator;
use Zend\Stdlib\Hydrator\ObjectProperty;

/**
 * Class HandleResultTraitTest
 */
class HandleResultTraitTest extends \PHPUnit_Framework_TestCase
{
    protected $handleResultTrait;

    public function setUp()
    {
        $this->handleResultTrait = $this->getObjectForTrait(
            'Matryoshka\Model\Wrapper\Mongo\Criteria\HandleResultTrait'
        );
    }

    public function testHandleResult()
    {
        $handleResultTrait = $this->handleResultTrait;

        $reflection = new \ReflectionClass($this->handleResultTrait);
        $reflMethod = $reflection->getMethod('handleResult');
        $reflMethod->setAccessible(true);

        $result = $reflMethod->invoke($handleResultTrait, null);
        $this->assertNull($result);

        $result = $reflMethod->invoke($handleResultTrait, true);
        $this->assertNull($result);

        $result = $reflMethod->invoke($handleResultTrait, []);
        $this->assertNull($result);

        $result = $reflMethod->invoke($handleResultTrait, ['ok' => 1, 'n' => 1]);
        $this->assertEquals(1, $result);

        $result = $reflMethod->invoke($handleResultTrait, ['ok' => 1, 'n' => 2, 'updatedExisting' => true]);
        $this->assertEquals(2, $result);

        $result = $reflMethod->invoke($handleResultTrait, ['ok' => 1, 'n' => 3], true);
        $this->assertEquals(3, $result);

        $this->setExpectedException('Matryoshka\Model\Wrapper\Mongo\Exception\MongoResultException');
        $reflMethod->invoke($handleResultTrait, ['err' => 1, 'errmsg' => 'error', 'code' => 100]);
    }
}
