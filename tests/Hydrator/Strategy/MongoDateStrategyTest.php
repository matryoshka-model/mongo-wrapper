<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Hydrator\Strategy;

use Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy\MongoDateStrategy;
use Matryoshka\Model\Exception\InvalidArgumentException;

/**
 * Class MongoDateStrategyTest
 */
class MongoDateStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCtor()
    {
        //Test default format
        $strategy = new MongoDateStrategy();
        $this->assertSame(\DateTime::ISO8601, $strategy->getFormat());

        $strategy = new MongoDateStrategy(\DateTime::ATOM);
        $this->assertSame(\DateTime::ATOM, $strategy->getFormat());
    }

    public function testExtract()
    {
        $strategy = new MongoDateStrategy();
        $now = time();

        $result = $strategy->extract(\DateTime::createFromFormat('U', $now));
        $this->assertEquals(new \MongoDate($now), $result);

        $this->setExpectedException(InvalidArgumentException::class);
        $result = $strategy->extract('test invalid value');
    }

    public function testHydrate()
    {
        $format   = \DateTime::ISO8601;
        $strategy = new MongoDateStrategy();
        $now = time();

        $result = $strategy->hydrate(new \MongoDate($now));
        $this->assertEquals(new \DateTime(date($format, $now)), $result);

        $this->setExpectedException(InvalidArgumentException::class);
        $result = $strategy->hydrate('test invalid value');
    }

    public function testGetSetFormat()
    {
        $strategy = new MongoDateStrategy();
        $format   = \DateTime::W3C;

        $this->assertSame($strategy, $strategy->setFormat($format));

        $this->assertEquals($format, $strategy->getFormat());
    }
}
