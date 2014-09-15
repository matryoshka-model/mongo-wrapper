<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Integration\Service;

use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\CreateMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\DeleteMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\FindMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Object\TestAsset\MongoObject;
use Zend\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

/**
 * Class MongoDbTest
 * @group integration
 */
class MongoDbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @var MongoObject
     */
    protected $obj;

    public function setUp()
    {
        $config = [
            'mongodb' => [
                'MongoDb\MongoWrapperTest' => [
                    'database' => 'mongowrappertest',
                ],
            ],
            'mongocollection' => [
                'MongoDataGateway\User' => [
                    'database'   => 'MongoDb\MongoWrapperTest',
                    'collection' => 'user'
                ],
                'MongoDataGateway\Restaurant' => [
                    'database'   => 'MongoDb\MongoWrapperTest',
                    'collection' => 'restaurant'
                ],
            ],
            'model' => [
                'ServiceModelUser' => [
                    'datagateway' => 'MongoDataGateway\User',
                    'resultset'   => 'Matryoshka\Model\ResultSet\HydratingResultSet',
                    'object'      => 'MongoObject',
                    'type'        => 'MatryoshkaTest\Model\Service\TestAsset\MyModel',
                    'hydrator'    => 'Zend\Stdlib\Hydrator\ObjectProperty'
                ],
            ],
        ];

        $sm = $this->serviceManager = new ServiceManager\ServiceManager(
            new ServiceManagerConfig([
                'abstract_factories' => [
                    'Matryoshka\Model\Service\ModelAbstractServiceFactory',
                    'Matryoshka\Model\Wrapper\Mongo\Service\MongoDbAbstractServiceFactory',
                    'Matryoshka\Model\Wrapper\Mongo\Service\MongoCollectionAbstractServiceFactory',
                ],
            ])
        );

        $sm->setService('Config', $config);
        $sm->setService('MatryoshkaTest\Model\Service\TestAsset\FakeDataGateway', new \MatryoshkaTest\Model\Service\TestAsset\FakeDataGateway);
        $sm->setService('Matryoshka\Model\ResultSet\ArrayObjectResultSet', new \Matryoshka\Model\ResultSet\ArrayObjectResultSet);
        $sm->setService('Matryoshka\Model\ResultSet\HydratingResultSet', new \Matryoshka\Model\ResultSet\HydratingResultSet);
        $sm->setService('Zend\Stdlib\Hydrator\ObjectProperty', new \Zend\Stdlib\Hydrator\ObjectProperty);
        $sm->setService('MongoObject', new MongoObject);


        $this->obj       = new MongoObject();
        $this->obj->name = "testMatrioska";
        $this->obj->age  = "8";

    }


    public function testIntegrationMongoDbInsert()
    {
        $criteria  = new CreateMongoCriteria();

        /* @var $serviceUser \Matryoshka\Model\Model */
        $serviceUser = $this->serviceManager->get('ServiceModelUser');
        $result = $serviceUser->save($criteria, $this->obj);
        $this->assertEquals(1, $result);
    }


    /**
     * @depends testIntegrationMongoDbInsert
     */
    public function testIntegrationMongoDbFind()
    {
        $criteria  = new FindMongoCriteria();

        /* @var $serviceUser \Matryoshka\Model\Model */
        $serviceUser = $this->serviceManager->get('ServiceModelUser');
        $result = $serviceUser->find($criteria);

        $this->assertNotEmpty($result->toArray());
    }

    /**
     * @depends testIntegrationMongoDbFind
     */
    public function testIntegrationMongoDbDelete()
    {
        $criteria  = new DeleteMongoCriteria();

        /* @var $serviceUser \Matryoshka\Model\Model */
        $serviceUser = $this->serviceManager->get('ServiceModelUser');
        $result = $serviceUser->delete($criteria);
        $this->assertGreaterThanOrEqual(1, $result);
    }


    /**
     * @depends testIntegrationMongoDbDelete
     */
    public function testIntegrationMongoDbFindEmpty()
    {
        $criteria  = new FindMongoCriteria();

        /* @var $serviceUser \Matryoshka\Model\Model */
        $serviceUser = $this->serviceManager->get('ServiceModelUser');
        $result = $serviceUser->find($criteria);

        $this->assertEmpty($result->toArray());
    }
}
