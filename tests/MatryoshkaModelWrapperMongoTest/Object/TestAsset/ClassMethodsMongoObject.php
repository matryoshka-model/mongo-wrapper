<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Object\TestAsset;

use Matryoshka\Model\Wrapper\Mongo\Object\AbstractMongoObject;
use Matryoshka\Model\Hydrator\Strategy\SetTypeStrategy;
use Matryoshka\Model\Wrapper\Mongo\Object\ClassMethodsTrait;
use Matryoshka\Model\Wrapper\Mongo\Object\ObjectPropertyTrait;

/**
 * Class ClassMethodsMongoObject
 */
class ClassMethodsMongoObject extends AbstractMongoObject
{
    use ClassMethodsTrait;

    /**
     * @var String
     */
    public $name;

    /**
     * @var int
     */
    public $age;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hydrator = $this->getHydrator();
        $intStrategy = new SetTypeStrategy('int', 'int');
        $this->hydrator->addStrategy('age', $intStrategy);
    }
}
