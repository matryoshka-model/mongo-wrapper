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
    use InputFilterAwareTrait;
    use ModelAwareTrait;

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
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $this->inputFilter = new InputFilter();
        }

        return $this->inputFilter;
    }

    /**
     * Save
     *
     * @return null|int
     */
    public function save()
    {
        $criteria = new ActiveRecordCriteria();
        $result = $this->getModel()->save($criteria, $this);
        return $result;
    }

    /**
     * Delete
     *
     * @return null|int
     * @throws Exception\RuntimeException
     */
    public function delete()
    {
        if (!$this->getId()) {
            throw new Exception\RuntimeException('An ID must be set prior to calling delete()');
        }

        $criteria = new ActiveRecordCriteria();
        $criteria->setId($this->getId());
        $result = $this->getModel()->delete($criteria);
        return $result;
    }
}
