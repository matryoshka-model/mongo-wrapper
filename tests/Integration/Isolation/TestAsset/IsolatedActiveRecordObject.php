<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset;

use Matryoshka\Model\Object\ActiveRecord\AbstractActiveRecord;
use Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated\ActiveRecordCriteria;
use Matryoshka\Model\Object\IdentityAwareTrait;
use Matryoshka\Model\Hydrator\ClassMethods;

/**
 * Class IsolatedActiveRecord
 */
class IsolatedActiveRecordObject extends AbstractActiveRecord
{

    protected $name;

    protected $description;

    public function __construct()
    {
        $this->setActiveRecordCriteriaPrototype(new ActiveRecordCriteria());
        $this->setHydrator(new ClassMethods());
    }

    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = (string) $description;
        return $this;
    }

    public function getDescription($description)
    {
        return $this->description;
    }

}
