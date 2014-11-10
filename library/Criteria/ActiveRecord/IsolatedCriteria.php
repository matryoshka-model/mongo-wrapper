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
use Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException;

/**
 * Class IsolatedCriteria
 *
 * Use the entire document as the query in the update() and remove() operations,
 * applying modification only if the fields have not changed in the collection since the find query,
 * otherwise a DocumentModifiedException will be thrown.
 *
 * In order to make this criteria working properly, the active record object must have the same
 * criteria instance for its whole lifecycle.
 *
 *
 * @link http://docs.mongodb.org/manual/tutorial/isolate-sequence-of-operations/
 *
 */
class IsolatedCriteria extends ActiveRecordCriteria
{

    /**
     * @var array
     */
    protected $initialStateCache = [];

    /**
     * {@inheritdoc}
     */
    public function apply(ModelInterface $model)
    {
        $results = parent::apply($model);
        $return = [];
        foreach ($results as $result) {
            $return[] = $this->initialStateCache[(string) $result['_id']] = $result;
        }
        return $return;
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

        //New document? Perform an insert
        if (!isset($data['_id']) || !isset($this->initialStateCache[(string) $data['_id']])) {
            $tmp = $data;  // passing a referenced variable to insert will fail in update the content
            $result = $dataGateway->insert($tmp, $this->getSaveOptions());
            $result = $this->handleResult($result);
            $data = $tmp;

            $this->initialStateCache[(string) $data['_id']] = $data;
            return $result;
        }

        //Else, update an existing document
        $initialState = $this->initialStateCache[(string) $data['_id']];
        $result = $dataGateway->update(
            $initialState,
            ['$set' => $data],
            array_merge($this->getSaveOptions(), ['multi' => false, 'upsert' => false])
        );
        $result = $this->handleResult($result);

        if ($result != 1) {
            throw new DocumentModifiedException('Document doesn\'t exist anymore or fields have changed in the collection since the find query');
        }

        $this->initialStateCache[(string) $data['_id']] = $data;
        return $result;
    }


    /**
     * {@inheritdoc}
     */
    public function applyDelete(ModelInterface $model)
    {
        $id = $this->extractId($model);

        if (!isset($this->initialStateCache[$id])) {
            throw new Exception\RuntimeException(
                'Document must be read prior to call delete()'
            );
        }

        $result = $model->getDataGateway()->remove($this->initialStateCache[$id]);
        $result = $this->handleResult($result, true);

        if ($result != 1) {
            throw new DocumentModifiedException('Document doesn\'t exist anymore or fields have changed in the collection since the find query');
        }

        unset($this->initialStateCache[$id]);
        return $result;
    }

}
