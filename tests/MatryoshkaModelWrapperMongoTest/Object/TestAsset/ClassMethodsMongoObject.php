<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Object\TestAsset;

use Matryoshka\Model\Wrapper\Mongo\Hydrator\ClassMethods;

/**
 * Class ClassMethodsMongoObject
 */
class ClassMethodsMongoObject extends ObjectPropertyMongoObject
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setHydrator(new ClassMethods());
        parent::__construct();
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param $age
     * @return int
     */
    public function setAge($age)
    {
        $this->age = $age;
        return $this;
    }
}
