<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated;

use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated\DocumentStoreAwareTrait;
use Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecord\ActiveRecordCriteria as BaseActiveRecordCriteria;

/**
 * Class ActiveRecordCriteria
 *
 * Isolated version of ActiveRecordCriteria
 * @see DocumentStore
 *
 */
class ActiveRecordCriteria extends BaseActiveRecordCriteria
{

    use DocumentStoreAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function apply(ModelInterface $model)
    {
        return $this->getDocumentStore()->initIsolationFromCursor(
            $model->getDataGateway(),
            parent::apply($model)
        );
    }


    /**
     * {@inheritdoc}
     */
    public function applyWrite(ModelInterface $model, array &$data)
    {
        unset($data['_id']);

        if ($this->id) {
            $data['_id'] = $this->extractId($model);
        }

        return $this->getDocumentStore()->isolatedUpsert(
            $model->getDataGateway(),
            $data,
            $this->getSaveOptions()
        );
    }


    /**
     * {@inheritdoc}
     */
    public function applyDelete(ModelInterface $model)
    {
        return $this->getDocumentStore()->isolatedRemove(
            $model->getDataGateway(),
            $this->extractId($model)
        );
    }

}
