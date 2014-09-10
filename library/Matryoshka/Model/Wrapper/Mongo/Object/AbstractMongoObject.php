<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Matryoshka\Model\Wrapper\Mongo\Object;

use Matryoshka\Model\AbstractModel;
use Matryoshka\Model\DataGatewayAwareTrait;
use Matryoshka\Model\Exception;
use Matryoshka\Model\ModelAwareInterface;
use Matryoshka\Model\ModelAwareTrait;
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Object\ActiveRecordInterface;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecordCriteria;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterAwareTrait;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorAwareTrait;
use Zend\Stdlib\Hydrator\ObjectProperty;

/**
 * Class AbstractMongoObject
 */
abstract class AbstractMongoObject implements
    HydratorAwareInterface,
    InputFilterAwareInterface,
    ModelAwareInterface,
    ActiveRecordInterface
{

    use HydratorAwareTrait;
    use InputFilterAwareTrait;
    use ModelAwareTrait;

    /**
     * @var string
     */
    public $_id;

    /**
     * Set Model
     *
     * @param ModelInterface $model
     * @return $this
     */
    public function setModel(ModelInterface $model)
    {
        if (!$model instanceof AbstractModel) {
            throw new Exception\InvalidArgumentException(
                'AbstractModel required in order to work with ActiveRecord'
            );
        }
        $this->model = $model;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new ObjectProperty();
        }

        return $this->hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $this->inputFilter = new InputFilter();
        }

        return $this->inputFilter;
    }

    /**
     * Get Id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set Id
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Object Exists In Database
     *
     * @return boolean
     */
    public function objectExistsInDatabase()
    {
        return empty($this->_id) ? false : true;
    }

    /**
     * Save
     *
     * @return null|int
     */
    public function save()
    {
        $criteria = new ActiveRecordCriteria();
        return $this->getModel()->save($criteria, $this);
    }

    /**
     * Delete
     *
     * @return null|int
     * @throws Exception\RuntimeException
     */
    public function delete()
    {
        if (!$this->objectExistsInDatabase()) {
            throw new Exception\RuntimeException("The asset must exists in database to be deleted");
        }

        $criteria = new ActiveRecordCriteria();
        $criteria->setId($this->_id);
        return $this->getModel()->delete($criteria);
    }


    /**
     * Get
     *
     * @param $name
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __get($name)
    {
        throw new \InvalidArgumentException('Not a valid field in this object: ' . $name);
    }

    /**
     * Set
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __set($name, $value)
    {
        throw new \InvalidArgumentException('Not a valid field in this object: ' . $name);
    }

    /**
     * Isset
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return false;
    }

    /**
     * Unset
     *
     * @param string $name
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __unset($name)
    {
        throw new \InvalidArgumentException('Not a valid field in this object: ' . $name);
    }
}
