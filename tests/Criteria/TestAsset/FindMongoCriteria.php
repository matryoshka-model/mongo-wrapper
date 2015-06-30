<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Criteria\TestAsset;

use Matryoshka\Model\Criteria\AbstractCriteria;
use Matryoshka\Model\ModelStubInterface;

/**
 * Class FindMongoCriteria
 */
class FindMongoCriteria extends AbstractCriteria
{
    /**
     * Apply
     * @param ModelStubInterface $model
     * @return mixed
     */
    public function apply(ModelStubInterface $model)
    {
        /* @var $dataGatewayMongo \MongoCollection */
        $dataGatewayMongo = $model->getDataGateway();
        return $dataGatewayMongo->find()->limit($this->limit)->skip($this->offset);
    }
}
