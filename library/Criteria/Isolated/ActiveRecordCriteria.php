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
        unset($data['_id']);

        if ($this->id) {
            $data['_id'] = $this->extractId($model);
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
