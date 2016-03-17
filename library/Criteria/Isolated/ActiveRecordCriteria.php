<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated;

use Matryoshka\Model\ModelStubInterface;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria as BaseActiveRecordCriteria;

/**
 * Class ActiveRecordCriteria
 *
 * Isolated version of the ActiveRecordCriteria
 * @see DocumentStore
 */
class ActiveRecordCriteria extends BaseActiveRecordCriteria
{

    use DocumentStoreAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function apply(ModelStubInterface $model)
    {
        return $this->getDocumentStore()->initIsolationFromCursor(
            $model->getDataGateway(),
            parent::apply($model)
        );
    }


    /**
     * {@inheritdoc}
     */
    public function applyWrite(ModelStubInterface $model, array &$data)
    {
        if ($this->hasId()) {
            $data['_id'] = $this->extractId($model);
        }

        if (array_key_exists('_id', $data) && null === $data['_id']) {
            unset($data['_id']);
        }

        return $this->getDocumentStore()->isolatedUpsert(
            $model->getDataGateway(),
            $data,
            $this->getMongoOptions()
        );
    }


    /**
     * {@inheritdoc}
     */
    public function applyDelete(ModelStubInterface $model)
    {
        return $this->getDocumentStore()->isolatedRemove(
            $model->getDataGateway(),
            $this->extractId($model),
            $this->getMongoOptions()
        );
    }
}
