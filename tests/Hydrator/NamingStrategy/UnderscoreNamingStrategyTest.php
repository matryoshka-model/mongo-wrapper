<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Hydrator\NamingStrategy;

use Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy\UnderscoreNamingStrategy;

/**
 * Class UnderscoreNamingStrategyTest
 */
class UnderscoreNamingStrategyTest  extends \PHPUnit_Framework_TestCase
{
    public function testExtract()
    {
        $strategy = new UnderscoreNamingStrategy();
        $this->assertSame('_id', $strategy->extract('id'));
        $this->assertSame('foo_bar_baz', $strategy->extract('fooBarBaz'));
    }

    public function testHydrate()
    {
        $strategy = new UnderscoreNamingStrategy();
        $this->assertSame('id', $strategy->hydrate('_id'));
        $this->assertSame('fooBarBaz', $strategy->hydrate('foo_bar_baz'));
    }
}
