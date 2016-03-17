<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Hydrator\Strategy;

use Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy\MongoBinDataStrategy;
use Matryoshka\Model\Exception\InvalidArgumentException;

/**
 * Class MongoBinDataTest
 */
class MongoBinDataTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetType()
    {
        $strategy = new MongoBinDataStrategy();
        $this->assertEquals(0, $strategy->getType());
        $this->assertSame($strategy, $strategy->setType(\MongoBinData::MD5));
        $this->assertSame(\MongoBinData::MD5, $strategy->getType());
    }

    public function testExtract()
    {
        $strategy = new MongoBinDataStrategy();
        $data = 'foo';

        $result = $strategy->extract($data);
        $this->assertInstanceOf('\MongoBinData', $result);
        $this->assertEquals($data, $result->bin);

        $result = $strategy->extract(null);
        $this->assertNull($result);
    }

    public function testHydrate()
    {
        $strategy = new MongoBinDataStrategy(\MongoBinData::CUSTOM);
        $mongoBinData = new \MongoBinData('foo', \MongoBinData::CUSTOM);

        $result = $strategy->hydrate($mongoBinData);
        $this->assertEquals($mongoBinData->bin, $result);

        $result = $strategy->hydrate(null);
        $this->assertNull($result);
    }
    
    public function testHydrateShouldThrowExceptionWhenInvalidType()
    {
        $strategy = new MongoBinDataStrategy(\MongoBinData::CUSTOM);
        $mongoBinData = new \MongoBinData('foo', \MongoBinData::CUSTOM);
        
        $this->setExpectedException(InvalidArgumentException::class);
        $strategy->hydrate('invalid type');
    }
}
