<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Hydrator;

use Matryoshka\Model\Wrapper\Mongo\Hydrator\ClassMethods;

/**
 * Class ClassMethodsTest
 */
class ClassMethodsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ctor
     */
    public function testCtor()
    {
        $hydrator = new ClassMethods();

        $this->assertFalse($hydrator->getUnderscoreSeparatedKeys());
        $this->assertInstanceOf(
            '\Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy\DefaultNamingStrategy',
            $hydrator->getNamingStrategy()
        );
        $this->assertInstanceOf(
            '\Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy\MongoIdStrategy',
            $hydrator->getStrategy('_id')
        );

        $hydrator = new ClassMethods(true);

        $this->assertTrue($hydrator->getUnderscoreSeparatedKeys());
        $this->assertInstanceOf(
            '\Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy\UnderscoreNamingStrategy',
            $hydrator->getNamingStrategy()
        );
        $this->assertInstanceOf(
            '\Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy\MongoIdStrategy',
            $hydrator->getStrategy('_id')
        );
    }

    public function testSetUnderscoreSeparatedKeys()
    {
        $hydrator = new ClassMethods();

        $this->assertSame($hydrator, $hydrator->setUnderscoreSeparatedKeys(true));
        $this->assertInstanceOf(
            '\Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy\UnderscoreNamingStrategy',
            $hydrator->getNamingStrategy()
        );

        $this->assertSame($hydrator, $hydrator->setUnderscoreSeparatedKeys(false));
        $this->assertInstanceOf(
            '\Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy\DefaultNamingStrategy',
            $hydrator->getNamingStrategy()
        );
    }
}
