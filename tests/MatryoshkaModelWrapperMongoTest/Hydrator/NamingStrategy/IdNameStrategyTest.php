<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Hydrator\NamingStrategy;


use Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy\IdNameStrategy;
class IdNameStrategyTest  extends \PHPUnit_Framework_TestCase
{

    public function testExtract()
    {
        $strategy = new IdNameStrategy();
        $this->assertSame('_id', $strategy->extract('id'));
        $this->assertSame('foo', $strategy->extract('foo'));
    }

    public function testHydrate()
    {
        $strategy = new IdNameStrategy();
        $this->assertSame('id', $strategy->hydrate('_id'));
        $this->assertSame('foo', $strategy->hydrate('foo'));
    }

}
