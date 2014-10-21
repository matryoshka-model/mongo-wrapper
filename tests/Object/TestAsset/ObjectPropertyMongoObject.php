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
use Matryoshka\Model\Object\AbstractActiveRecord;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecordCriteria;
use Matryoshka\Model\Wrapper\Mongo\Object\ObjectPropertyTrait;

/**
 * Class ObjectPropertyMongoObject
 */
class ObjectPropertyMongoObject extends AbstractActiveRecord
{

    public $_id;

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
        $intStrategy = new SetTypeStrategy('int', 'int');
        $this->getHydrator()->addStrategy('age', $intStrategy);
        $this->setActiveRecordCriteriaPrototype(new ActiveRecordCriteria());
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }
}
