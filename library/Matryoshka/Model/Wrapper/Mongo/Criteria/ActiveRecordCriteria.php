<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Matryoshka\Model\Wrapper\Mongo\Criteria;

use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\Exception;
use Matryoshka\Model\ModelInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Matryoshka\Model\Wrapper\Mongo\Criteria\Exception\MongoResultException;

/**
 * Class ObjectGatewayCriteria
 */
class ActiveRecordCriteria extends AbstractCriteria
{
    /**
     * @var array
     */
    protected $saveOptions = [];

    /**
     * {@inheritdoc}
     */
    public function apply(ModelInterface $model)
    {
        /** @var $dataGateway \MongoCollection */
        $dataGateway = $model->getDataGateway();
        return $dataGateway->find(['_id' => $this->extractId($model)])->limit(1);
    }

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    protected function extractId(ModelInterface $model)
    {
        if (!$model->getHydrator() instanceof AbstractHydrator) {
                throw new Exception\RuntimeException(
                    'Hydrator must be an instance of \Zend\Stdlib\Hydrator\AbstractHydrator'
                );
        }

        return $model->getHydrator()->extractValue('_id', $this->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function applyWrite(ModelInterface $model, array &$data)
    {
        if (array_key_exists('_id', $data) && $data['_id'] === null) {
            unset($data['_id']);
        }

        // FIXME: handle result
        $tmp = $data;  // passing a referenced variable to save will fail in update the content
        $result = $model->getDataGateway()->save($tmp, $this->getSaveOptions());
        $data = $tmp;
        $this->hydrateId($model, $data['_id'], $data);
        return $this->handleResult($result);
    }

    /**
     * @return array
     */
    public function getSaveOptions()
    {
        return $this->saveOptions;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setSaveOptions(array $options)
    {
        $this->saveOptions = $options;
        return $this;
    }

    /**
     * @param ModelInterface $model
     * @param $value
     * @param null $data
     * @return mixed
     */
    protected function hydrateId(ModelInterface $model, $value, $data = null)
    {
        if (!$model->getHydrator() instanceof AbstractHydrator) {
            throw new Exception\RuntimeException(
                'Hydrator must be an instance of \Zend\Stdlib\Hydrator\AbstractHydrator'
            );
        }

        $this->id = $model->getHydrator()->hydrateValue('_id', $value, $data);
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function applyDelete(ModelInterface $model)
    {
        if (!$this->id) {
            throw new Exception\RuntimeException(
                'An id must be set in order to delete an object'
            );
        }

        $result = $model->getDataGateway()->remove(['_id' => $this->extractId($model)]);
        return $this->handleResult($result);
    }

    /**
     * @param $result
     * @return int|null
     */
    protected function handleResult($result)
    {
        //No info available
        if ($result === true) {
            return null;
        }

        if (is_array($result)) {
            if (isset($result['ok']) && $result['ok']) { //This should almost always be 1 (unless last_error itself failed)
                return isset($result['n']) ? (int) $result['n'] : null;
            }

            if (isset($result['err']) && $result['err'] !== null) {
                throw new MongoResultException($result['errmsg'], $result['code']);
            }
        }

        return null;

    }
}
