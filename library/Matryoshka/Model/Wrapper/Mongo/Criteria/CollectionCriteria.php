<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Matryoshka\Model\Wrapper\Mongo\Criteria;

use Matryoshka\Model\Criteria\AbstractCriteria;
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Wrapper\Mongo\Paginator\MongoPaginatorAdapter;

/**
 * Class CollectionCriteria
 */
class CollectionCriteria extends AbstractCriteria
{
    /**
     * @var array
     */
    protected $query;

    /**
     * @var array
     */
    protected $fields;


    public function getPaginatorAdapter(ModelInterface $model)
    {
        $resultSet = clone $model->getResultSetPrototype();
        $resultSet->initialize($this->apply($model));
        return new MongoPaginatorAdapter($resultSet);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ModelInterface $model)
    {
        /** @var $dataGateway \MongoCollection */
        $dataGateway = $model->getDataGateway();
        return $cursor = $dataGateway->find()->limit($this->limit)->skip($this->offset);
    }

}

