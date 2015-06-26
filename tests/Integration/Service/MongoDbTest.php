<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Integration\Service;

use Matryoshka\Model\Wrapper\Mongo\ResultSet\HydratingResultSet;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\CreateMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\DeleteMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\FindMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Object\TestAsset\MongoObject;
use MatryoshkaTest\Model\Service\TestAsset\FakeDataGateway;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager;
use Zend\Stdlib\Hydrator\ObjectProperty;

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
            'matryoshka-models' => [
                'ServiceModelUser' => [
                    'datagateway' => 'MongoDataGateway\User',
                    'resultset'   => 'Matryoshka\Model\Wrapper\Mongo\ResultSet\HydratingResultSet',
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
        $sm->setService('MatryoshkaTest\Model\Service\TestAsset\FakeDataGateway', new FakeDataGateway());
        $sm->setService('Matryoshka\Model\Wrapper\Mongo\ResultSet\HydratingResultSet', new HydratingResultSet());
        $sm->setService('Zend\Stdlib\Hydrator\ObjectProperty', new ObjectProperty());
        $sm->setService('MongoObject', new MongoObject);

        $this->obj       = new MongoObject();
        $this->obj->name = "testMatrioska";
        $this->obj->age  = "8";

        $sm->get('MongoDb\MongoWrapperTest')->drop();
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
        $result = $serviceUser->save(new CreateMongoCriteria, $this->obj);

        $result = $serviceUser->find($criteria);

        $this->assertCount(1, $result);
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

    /**
     * @depends testIntegrationMongoDbFindEmpty
     */
    public function testIntegrationMongoCursorCount()
    {
        $obj1 = clone $this->obj;
        $obj1->foo = 1;

        $obj2 = clone $this->obj;
        $obj2->foo = 2;

        $obj3 = clone $this->obj;
        $obj3->foo = 3;

        /* @var $serviceUser \Matryoshka\Model\Model */
        $serviceUser = $this->serviceManager->get('ServiceModelUser');
        $serviceUser->save(new CreateMongoCriteria, $obj1);
        $serviceUser->save(new CreateMongoCriteria, $obj2);
        $serviceUser->save(new CreateMongoCriteria, $obj3);

        $result = $serviceUser->find(new FindMongoCriteria);
        $this->assertCount(3, $result);

        $findCriteria = new FindMongoCriteria();
        $findCriteria->setLimit(1);
        $result = $serviceUser->find($findCriteria);
        $this->assertCount(1, $result);
    }
}
