<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Hydrator;

use Matryoshka\Model\Wrapper\Mongo\Hydrator\ClassMethods;

class ClassMethodsTest  extends \PHPUnit_Framework_TestCase
{

    public function test__construct()
    {
        $hydrator = new ClassMethods();

        $this->assertInstanceOf('\Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy\IdNameStrategy', $hydrator->getNamingStrategy());
        $this->assertInstanceOf('\Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy\MongoIdStrategy', $hydrator->getStrategy('_id'));
    }

}
