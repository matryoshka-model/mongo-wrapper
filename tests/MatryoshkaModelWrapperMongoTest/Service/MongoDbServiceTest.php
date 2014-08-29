<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Service;

use Matryoshka\Model\Wrapper\Mongo\Service\MongoCollectionAbstractServiceFactory;
use Matryoshka\Model\Wrapper\Mongo\Service\MongoDbAbstractServiceFactory;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\CreateMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\DeleteMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Object\TestAsset\MongoObject;
use Zend\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

class MongoDbServiceTest  extends \PHPUnit_Framework_TestCase
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
                'MongoDb\Mangione' => [
                    'database' => 'test',
                ],
            ],
            'mongocollection' => [
                'MongoDataGateway\User' => [
                    'database'   => 'MongoDb\Mangione',
                    'collection' => 'userMatrioska'
                ],
                'MongoDataGateway\Restaurant' => [
                    'database'   => 'MongoDb\Mangione',
                    'collection' => 'restaurantMatrioska'
                ],
            ],
            'model' => [
                'ServiceModelUser' => [
                    'datagateway' => 'MongoDataGateway\User',
                    'resultset'   => 'Matryoshka\Model\ResultSet\HydratingResultSet',
                    'object'      => 'ArrayObject',
                    'hydrator'    => 'Zend\Stdlib\Hydrator\ArraySerializable',
                    'type'        => 'MatryoshkaTest\Model\Service\TestAsset\MyModel',
                ],
            ],
        ];


        $sm = $this->serviceManager = new ServiceManager\ServiceManager(
            new ServiceManagerConfig([
                'abstract_factories' => [
                    'Matryoshka\Model\Service\ModelAbstractServiceFactory',
                    'Matryoshka\Model\Wrapper\Mongo\Service\MongoDbAbstractServiceFactory',
                    'Matryoshka\Model\Wrapper\Mongo\Service\MongoCollectionAbstractServiceFactory',
                ]
            ])
        );

        $sm->setService('Config', $config);
        $sm->setService('MatryoshkaTest\Model\Service\TestAsset\FakeDataGateway', new \MatryoshkaTest\Model\Service\TestAsset\FakeDataGateway);
        $sm->setService('Matryoshka\Model\ResultSet\ArrayObjectResultSet', new \Matryoshka\Model\ResultSet\ArrayObjectResultSet);
        $sm->setService('Matryoshka\Model\ResultSet\HydratingResultSet', new \Matryoshka\Model\ResultSet\HydratingResultSet);
        $sm->setService('Zend\Stdlib\Hydrator\ArraySerializable', new \Zend\Stdlib\Hydrator\ArraySerializable);
        $sm->setService('ArrayObject', new \ArrayObject);


        $this->obj       = new MongoObject();
        $this->obj->name = "testMatrioska";
        $this->obj->age  = "8";

    }

    /**
     * @return void
     */
    public function testCanCreateServiceMongoDb()
    {
        $factory = new MongoDbAbstractServiceFactory();
        $serviceLocator = $this->serviceManager;

        $this->assertFalse($factory->canCreateServiceWithName($serviceLocator, 'mongodbNotExist', 'MongoDb\NonExist'));
        $this->assertTrue($factory->canCreateServiceWithName($serviceLocator, 'mongodbMangione', 'MongoDb\Mangione'));
    }

    /**
     * @depends testCanCreateServiceMongoDb
     */
    public function testCanCreateServiceMongoCollection()
    {
        $factory = new MongoCollectionAbstractServiceFactory();
        $serviceLocator = $this->serviceManager;

        $this->assertFalse($factory->canCreateServiceWithName($serviceLocator, 'datagatewayNotExist', 'MongoDataGateway\NotExist'));
        $this->assertTrue($factory->canCreateServiceWithName($serviceLocator, 'datagatewayUser', 'MongoDataGateway\User'));
    }
}
