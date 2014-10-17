<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Criteria;

use Matryoshka\Model\Exception;
use Matryoshka\Model\ModelInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Matryoshka\Model\Criteria\AbstractCriteria;

/**
 * Class FindAllCriteria
 */
class FindAllCriteria extends AbstractCriteria
{
    /**
     * Mongo selection criteria
     *
     * By default this criteria selects all documents in a collection.
     * Extended classes can override selection criteria using query operators
     * in order to restrict the selection.
     *
     * @see http://docs.mongodb.org/manual/reference/method/db.collection.find/
     *
     * @var array
     */
    protected $selectionCriteria = [];


    /**
     * Mongo sort params
     *
     * Extended class can specifies the order in which the query returns matching documents.
     *
     * @see http://docs.mongodb.org/manual/reference/method/cursor.sort/
     *
     * @var array
     */
    protected $sortParams = [];

    /**
     * @param ModelInterface $model
     * @param $name
     * @param $value
     * @param null $object
     * @return mixed
     */
    protected function extractValue(ModelInterface $model, $name, $value, $object = null)
    {
        if (!$model->getHydrator() instanceof AbstractHydrator) {
            throw new Exception\RuntimeException(sprintf(
                'Hydrator must be an instance of "%s"; detected "%s"',
                '\Zend\Stdlib\Hydrator\AbstractHydrator',
                get_class($model->getHydrator())
            ));
        }

        return $model->getHydrator()->extractValue($name, $value, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ModelInterface $model)
    {
        /** @var $dataGateway \MongoCollection */
        $dataGateway = $model->getDataGateway();

        $cursor = $dataGateway->find($this->selectionCriteria);
        if (!empty($this->sortParams)) {
            $cursor->sort($this->sortParams);
        }

        return $cursor->limit($this->limit)->skip($this->offset);
    }
}
