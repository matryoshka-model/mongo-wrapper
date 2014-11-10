<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord;

use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\Exception;
use Matryoshka\Model\ModelInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Matryoshka\Model\Wrapper\Mongo\Criteria\HandleResultTrait;

/**
 * Class ActiveRecordCriteria
 */
class ActiveRecordCriteria extends AbstractCriteria
{
    use HandleResultTrait;

    /**
     * @var array
     */
    protected $saveOptions = [];

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
     * {@inheritdoc}
     */
    public function apply(ModelInterface $model)
    {
        /** @var $dataGateway \MongoCollection */
        $dataGateway = $model->getDataGateway();
        return $dataGateway->find(['_id' => $this->extractId($model)])->limit(1);
    }

    /**
     * {@inheritdoc}
     */
    public function applyWrite(ModelInterface $model, array &$data)
    {
        /** @var $dataGateway \MongoCollection */
        $dataGateway = $model->getDataGateway();

        unset($data['_id']);

        if ($this->id) {
            $data['_id'] = $this->extractId($model);
        }

        $tmp = $data;  // passing a referenced variable to save will fail in update the content
        $result = $dataGateway->save($tmp, $this->getSaveOptions());
        $data = $tmp;
        return $this->handleResult($result);
    }

    /**
     * {@inheritdoc}
     */
    public function applyDelete(ModelInterface $model)
    {
        $result = $model->getDataGateway()->remove(['_id' => $this->extractId($model)]);
        return $this->handleResult($result, true);
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
}
