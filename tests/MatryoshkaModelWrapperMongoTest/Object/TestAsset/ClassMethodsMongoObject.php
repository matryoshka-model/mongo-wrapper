<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Object\TestAsset;

use Matryoshka\Model\Hydrator\Strategy\SetTypeStrategy;
use Matryoshka\Model\Wrapper\Mongo\Object\ClassMethodsTrait;
use Matryoshka\Model\Wrapper\Mongo\Object\ObjectPropertyTrait;
use Matryoshka\Model\Object\AbstractActiveRecord;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecordCriteria;
use Matryoshka\Model\Wrapper\Mongo\Hydrator\ClassMethods;
echo 'ciao';
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $name;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setAge($age)
    {
        return $this->age;
    }


}
