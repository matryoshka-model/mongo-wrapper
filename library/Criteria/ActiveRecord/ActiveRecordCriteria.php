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
use Matryoshka\Model\ModelStubInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Matryoshka\Model\Wrapper\Mongo\Criteria\HandleResultTrait;

/**
 * Class ActiveRecordCriteria
 */
class ActiveRecordCriteria extends AbstractCriteria
{
    use HandleResultTrait;

    /**
     * Mongo projection
     *
     * Optional. Controls the fields to return, or the projection.
     * Extended classes can override this property in order to
     * control the fields to return.
     *
     * @var array
     */
    protected $projectionFields = [];

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
    public function apply(ModelStubInterface $model)
    {
        /** @var $dataGateway \MongoCollection */
        $dataGateway = $model->getDataGateway();
        return $dataGateway->find(
            ['_id' => $this->extractId($model)],
            $this->projectionFields
        )->limit(1);
    }

    /**
     * {@inheritdoc}
     */
    public function applyWrite(ModelStubInterface $model, array &$data)
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
    public function applyDelete(ModelStubInterface $model)
    {
        $result = $model->getDataGateway()->remove(['_id' => $this->extractId($model)]);
        return $this->handleResult($result, true);
    }

    /**
     * @param ModelStubInterface $model
     * @return mixed
     */
    protected function extractId(ModelStubInterface $model)
    {
        if (!$model->getHydrator() instanceof AbstractHydrator) {
            throw new Exception\RuntimeException(
                'Hydrator must be an instance of \Zend\Stdlib\Hydrator\AbstractHydrator'
            );
        }

        return $model->getHydrator()->extractValue('_id', $this->getId());
    }
}
