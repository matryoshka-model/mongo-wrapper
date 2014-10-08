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
use Matryoshka\Model\Wrapper\Mongo\Criteria\HandleResultTrait;

/**
 * Class DeleteMongoCriteria
 */
class DeleteMongoCriteria implements DeletableCriteriaInterface
{
    use HandleResultTrait;
    /**
     * {@inheritdoc}
     */
    public function applyDelete(ModelInterface $model)
    {
        /* @var $dataGatewayMongo \MongoCollection */
        $dataGatewayMongo = $model->getDataGateway();
        $result = $dataGatewayMongo->remove([]);
        return $this->handleResult($result);
    }
}
