<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Hydrator\Strategy;

use Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy\MongoIdStrategy;
use Matryoshka\Model\Exception\InvalidArgumentException;

/**
 * Class MongoIdStrategyTest
 */
class MongoIdStrategyTest extends \PHPUnit_Framework_TestCase
{

    public function testExtract()
    {
        $strategy = new MongoIdStrategy();
        $mongoId = new \MongoId();

        $result = $strategy->extract((string) $mongoId);
        $this->assertEquals($mongoId, $result);

        $result = $strategy->extract(null);
        $this->assertNull($result);
        
        $this->setExpectedException(InvalidArgumentException::class);
        $strategy->extract(['not-a-string' => 'invalid-type']);
    }

    public function testHydrate()
    {
        $strategy = new MongoIdStrategy();
        $mongoId = new \MongoId();

        $result = $strategy->hydrate($mongoId);
        $this->assertEquals((string) $mongoId, $result);

        $result = $strategy->hydrate(null);
        $this->assertNull($result);
        
        $this->setExpectedException(InvalidArgumentException::class);
        $strategy->hydrate('invalid-type');
    }

}
