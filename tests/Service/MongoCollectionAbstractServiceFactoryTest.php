<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Service;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * Class MongoCollectionAbstractServiceFactoryTest
 */
class MongoCollectionAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @return array
     */
    public function providerValidService()
    {
        return [
            ['MongoDataGateway\User'],
        ];
    }

    /**
     * @return array
     */
    public function providerInvalidService()
    {
        return [
            ['MongoDataGateway\Invalid'],
        ];
    }

    public function setUp()
    {
        $config = [
            'mongodb' => [
                'MongoDb\MongoWrapperTest' => [
                    'database' => 'mongodbabstractservicetest',
                    'username' => '',
                    'password' => '',
                    'hosts' => 'localhost:27017',
                    'options' => [
                        'connect' => false, // NOTE: should the constructor return before connecting?
                    ]
                ],
            ],
            'mongocollection' => [
                'MongoDataGateway\User' => [
                    'database'   => 'MongoDb\MongoWrapperTest',
                    'collection' => 'user'
                ],
            ],
        ];

        $sm = $this->serviceManager = new ServiceManager(
            new Config([
                'abstract_factories' => [
                    'Matryoshka\Model\Wrapper\Mongo\Service\MongoDbAbstractServiceFactory',
                    'Matryoshka\Model\Wrapper\Mongo\Service\MongoCollectionAbstractServiceFactory',
                ]
            ])
        );

        $sm->setService('Config', $config);
    }

    /**
     * @param $service
     * @dataProvider providerValidService
     */
    public function testCreateService($service)
    {
        $actual = $this->serviceManager->get($service);
        $this->assertInstanceOf('\MongoCollection', $actual);
    }


    /**
     * @param string $service
     * @dataProvider providerInvalidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testInvalidService($service)
    {
        $this->serviceManager->get($service);
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testNullConfig($service)
    {
        $sl = new ServiceManager(
            new Config(
                [
                    'abstract_factories' => [
                        'Matryoshka\Model\Wrapper\Mongo\Service\MongoDbAbstractServiceFactory',
                        'Matryoshka\Model\Wrapper\Mongo\Service\MongoCollectionAbstractServiceFactory',
                    ]
                ]
            )
        );
        $sl->get($service);
    }

    /**
     * @param string $service
     * @dataProvider providerValidService
     * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testEmptyConfig($service)
    {
        $sl = new ServiceManager(
            new Config(
                [
                    'abstract_factories' => [
                        'Matryoshka\Model\Wrapper\Mongo\Service\MongoDbAbstractServiceFactory',
                        'Matryoshka\Model\Wrapper\Mongo\Service\MongoCollectionAbstractServiceFactory',
                    ]
                ]
            )
        );
        $sl->setService('Config', []);
        $sl->get($service);
    }
}
