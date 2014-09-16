<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Criteria\TestAsset;

use Matryoshka\Model\Criteria\WritableCriteriaInterface;
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Wrapper\Mongo\Criteria\HandleResultTrait;

/**
 * Class CreateMongoCriteria
 */
class CreateMongoCriteria implements WritableCriteriaInterface
{
    use HandleResultTrait;

    /**
     * {@inheritdoc}
     */
    public function applyWrite(ModelInterface $model, array &$data)
    {
        unset($data['_id']);
        /* @var $dataGatewayMongo \MongoCollection */
        $dataGatewayMongo = $model->getDataGateway();
        $result = $dataGatewayMongo->save($data);
        return $this->handleResult($result);
    }
}
