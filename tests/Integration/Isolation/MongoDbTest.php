<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Integration\Isolation;

use Matryoshka\Model\Wrapper\Mongo\ResultSet\HydratingResultSet;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\CreateMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\DeleteMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Criteria\TestAsset\FindMongoCriteria;
use MatryoshkaModelWrapperMongoTest\Object\TestAsset\MongoObject;
use MatryoshkaTest\Model\Service\TestAsset\FakeDataGateway;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated\ActiveRecordCriteria;
use Matryoshka\Model\Wrapper\Mongo\Hydrator\ClassMethods;
use MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset\IsolatedActiveRecordObject;

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
                'MongoDataGateway\Isolation' => [
                    'database'   => 'MongoDb\MongoWrapperTest',
                    'collection' => 'isolation'
                ],
                'MongoDataGateway\IsolationA' => [
                    'database'   => 'MongoDb\MongoWrapperTest',
                    'collection' => 'isolation'
                ],
                'MongoDataGateway\IsolationB' => [
                    'database'   => 'MongoDb\MongoWrapperTest',
                    'collection' => 'isolation'
                ],
            ],
            'matryoshka-models' => [
                'IsolatedModel' => [
                    'datagateway' => 'MongoDataGateway\Isolation',
                    'resultset'   => 'Matryoshka\Model\Wrapper\Mongo\ResultSet\HydratingResultSet',
                    'object'      => 'MongoObject',
                    'type'        => 'MatryoshkaTest\Model\Service\TestAsset\MyModel',
                    'hydrator'    => 'Zend\Stdlib\Hydrator\ObjectProperty'
                ],
                'IsolatedModelA' => [
                    'datagateway' => 'MongoDataGateway\IsolationA',
                    'resultset'   => 'Matryoshka\Model\Wrapper\Mongo\ResultSet\HydratingResultSet',
                    'object'      => 'IsolatedActiveRecordObject',
                    'type'        => 'MatryoshkaTest\Model\Service\TestAsset\MyModel',
                    'hydrator'    => 'Matryoshka\Model\Wrapper\Mongo\Hydrator\ClassMethods'
                ],
                'IsolatedModelB' => [
                    'datagateway' => 'MongoDataGateway\IsolationB',
                    'resultset'   => 'Matryoshka\Model\Wrapper\Mongo\ResultSet\HydratingResultSet',
                    'object'      => 'IsolatedActiveRecordObject',
                    'type'        => 'MatryoshkaTest\Model\Service\TestAsset\MyModel',
                    'hydrator'    => 'Matryoshka\Model\Wrapper\Mongo\Hydrator\ClassMethods'
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
        $sm->setService('Matryoshka\Model\Wrapper\Mongo\Hydrator\ClassMethods', new ClassMethods());
        $sm->setService('MongoObject', new MongoObject);
        $sm->setService('IsolatedActiveRecordObject', new IsolatedActiveRecordObject);

        $this->obj       = new MongoObject();
        $this->obj->name = "testMatrioska";
        $this->obj->age  = "8";

        $sm->get('MongoDb\MongoWrapperTest')->drop();
    }

    public function testUpdateIfCurrent()
    {
        $model = $this->serviceManager->get('IsolatedModel');
        $criteria = new ActiveRecordCriteria();

        // insert
        $this->assertEquals(1, $model->save($criteria, $this->obj));
        $criteria->setId($this->obj->_id);

        // update (document not modified from outside)
        $this->obj->name = "foo";
        $this->assertEquals(1, $model->save($criteria, $this->obj));

        // simulate document modified outside the isolation scope
        $this->serviceManager->get('MongoDataGateway\Isolation')->update(['name' => $this->obj->name], ['$set' => ['age' => "9"]]);


        $this->obj->name = "test";

        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $model->save($criteria, $this->obj);
    }

    public function testInterleavedOperationsCase1()
    {
        $modelA = $this->serviceManager->get('IsolatedModelA');
        $modelB = $this->serviceManager->get('IsolatedModelB');


        // ensure document
        $obj = new IsolatedActiveRecordObject();
        $obj->setModel($modelA);
        $obj->setName('foo');
        $obj->save();



        //Read from A
        $criteria = new ActiveRecordCriteria();
        $criteria->setId($obj->getId());
        $objA = $modelA->find($criteria)->current();
        $this->assertInstanceOf('\MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset\IsolatedActiveRecordObject', $objA);


        //Read from B
        $criteria = new ActiveRecordCriteria();
        $criteria->setId($obj->getId());
        $objB = $modelB->find($criteria)->current();
        $this->assertInstanceOf('\MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset\IsolatedActiveRecordObject', $objB);

        //Write from B
        $objB->setName('B');
        $this->assertEquals(1, $objB->save());

        //Write from A
        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $objA->setName('A');
        $objA->save();
    }

    public function testInterleavedOperationsCase2()
    {
        $modelA = $this->serviceManager->get('IsolatedModelA');
        $modelB = $this->serviceManager->get('IsolatedModelB');

        // ensure document
        $obj = new IsolatedActiveRecordObject();
        $obj->setModel($modelA);
        $obj->setName('foo');
        $obj->save();


        //Read from A
        $criteria = new ActiveRecordCriteria();
        $criteria->setId($obj->getId());
        $objA = $modelA->find($criteria)->current();
        $this->assertInstanceOf('\MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset\IsolatedActiveRecordObject', $objA);


        //Read from B
        $criteria = new ActiveRecordCriteria();
        $criteria->setId($obj->getId());
        $objB = $modelB->find($criteria)->current();
        $this->assertInstanceOf('\MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset\IsolatedActiveRecordObject', $objB);

        //Write from A
        $objA->setName('A');
        $this->assertEquals(1, $objA->save());

        //Write from B
        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $objB->setName('B');
        $objB->save();
    }

    public function testInterleavedDelete()
    {
        $modelA = $this->serviceManager->get('IsolatedModelA');
        $modelB = $this->serviceManager->get('IsolatedModelB');

        // ensure document
        $obj = new IsolatedActiveRecordObject();
        $obj->setModel($modelA);
        $obj->setName('foo');
        $obj->save();


        //Read from A
        $criteria = new ActiveRecordCriteria();
        $criteria->setId($obj->getId());
        $objA = $modelA->find($criteria)->current();
        $this->assertInstanceOf('\MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset\IsolatedActiveRecordObject', $objA);


        //Read from B
        $criteria = new ActiveRecordCriteria();
        $criteria->setId($obj->getId());
        $objB = $modelB->find($criteria)->current();
        $this->assertInstanceOf('\MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset\IsolatedActiveRecordObject', $objB);

        //Delete from A
        $objA->setName('A');
        $this->assertEquals(1, $objA->delete());

        //Write from B
        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $objB->setName('B');
        $objB->save();
    }

    public function testInterleavedDelete2()
    {
        $modelA = $this->serviceManager->get('IsolatedModelA');
        $modelB = $this->serviceManager->get('IsolatedModelB');

        // ensure document
        $obj = new IsolatedActiveRecordObject();
        $obj->setModel($modelA);
        $obj->setName('foo');
        $obj->save();


        //Read from A
        $criteria = new ActiveRecordCriteria();
        $criteria->setId($obj->getId());
        $objA = $modelA->find($criteria)->current();
        $this->assertInstanceOf('\MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset\IsolatedActiveRecordObject', $objA);


        //Read from B
        $criteria = new ActiveRecordCriteria();
        $criteria->setId($obj->getId());
        $objB = $modelB->find($criteria)->current();
        $this->assertInstanceOf('\MatryoshkaModelWrapperMongoTest\Integration\Isolation\TestAsset\IsolatedActiveRecordObject', $objB);

        //Delete from A
        $objA->setName('A');
        $this->assertEquals(1, $objA->delete());

        //Write from B
        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $objB->setName('B');
        $objB->delete();
    }
}
