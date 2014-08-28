<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Object\TestAsset;

use Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy\IntStrategy;
use Matryoshka\Model\Wrapper\Mongo\Object\AbstractMongoObject;

class MongoObject extends AbstractMongoObject
{
    /**
     * @var String
     */
    public $name;

    /**
     * @var int
     */
    public $age;

    function __construct()
    {
        $this->hydrator = parent::getHydrator();
        $this->hydrator->addStrategy('age', new IntStrategy());
    }
}
