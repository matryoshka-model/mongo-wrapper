<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Criteria\TestAsset;

use Matryoshka\Model\Criteria\DeletableCriteriaInterface;
use Matryoshka\Model\ModelInterface;

/**
 * Class DeleteMongoCriteria
 */
class DeleteMongoCriteria implements DeletableCriteriaInterface
{
    /**
     * {@inheritdoc}
     */
    public function applyDelete(ModelInterface $model)
    {
        /* @var $dataGatewayMongo \MongoCollection */
        $dataGatewayMongo = $model->getDataGateway();
        return $dataGatewayMongo->remove(array());
    }
}
