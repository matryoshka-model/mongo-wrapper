<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Model\Wrapper\Mongo\Criteria\Isolated;

use Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated\DocumentStore;
use MatryoshkaModelWrapperMongoTest\TestAsset\MongoCollectionMockProxy;

/**
 * Class DocumentStoreTest
 */
class DocumentStoreTest extends \PHPUnit_Framework_TestCase
{
    protected static $oldErrorLevel;

    protected static function disableStrictErrors()
    {
        self::$oldErrorLevel = error_reporting();
        error_reporting(self::$oldErrorLevel & ~E_STRICT);
    }

    protected static function restoreErrorReportingLevel()
    {
        error_reporting(self::$oldErrorLevel);
    }

    protected static $sharedDataGateway;

    /**
     * @var DocumentStore
     */
    protected $documentStore;
    protected $mongoCollection;

    public static function setUpBeforeClass()
    {
        self::disableStrictErrors();
        self::$sharedDataGateway = new MongoCollectionMockProxy();
        self::restoreErrorReportingLevel();
    }

    /** @var \PHPUnit_Framework_MockObject_MockObject $mongoCollectionMock */
    protected $mongoCollectionMock;


    public function setUp()
    {
        $this->documentStore = DocumentStore::getSharedInstance();
        DocumentStore::resetSharedInstance();

        $mongoCollectionMock = $this->getMockBuilder('\MongoCollection')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'find', 'remove', 'insert', 'update', 'getName'])
            ->getMock();

        $mongoCollectionMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('testCollection'));

        $this->mongoCollectionMock = $mongoCollectionMock;

        self::$sharedDataGateway->__MongoCollectionMockProxy__setMock($mongoCollectionMock);
        $this->mongoCollection = self::$sharedDataGateway;
    }

    /**
     * @param $id
     * @param array $document
     */
    protected function assertHasDocumentCache($id, array $document)
    {
        $this->assertTrue(
            $this->documentStore->has(self::$sharedDataGateway, $id)
        );
        $this->assertSame(
            $document,
            $this->documentStore->get(self::$sharedDataGateway, $id)
        );
    }

    public function testSharedInstace()
    {
        $this->assertFalse(DocumentStore::hasSharedInstance());

        $sharedInstance = DocumentStore::getSharedInstance();
        $this->assertInstanceOf('\Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated\DocumentStore', $sharedInstance);
        $this->assertTrue(DocumentStore::hasSharedInstance());

        DocumentStore::resetSharedInstance();
        $this->assertFalse(DocumentStore::hasSharedInstance());

        $sharedInstance = clone $sharedInstance;
        DocumentStore::setSharedInstance($sharedInstance);
        $this->assertTrue(DocumentStore::hasSharedInstance());
        $this->assertSame($sharedInstance, DocumentStore::getSharedInstance());
    }


    public function testHasGet()
    {
        $id = 'foo';
        $data = ['test' => 'test'];

        $this->assertFalse($this->documentStore->has($this->mongoCollection, $id));
        $this->assertNull($this->documentStore->get($this->mongoCollection, $id));

        $reflClass = new \ReflectionClass($this->documentStore);
        $saveMethod = $reflClass->getMethod('save');
        $saveMethod->setAccessible(true);
        $saveMethod->invoke($this->documentStore, $this->mongoCollection, $id, $data);

        $this->assertTrue($this->documentStore->has($this->mongoCollection, $id));
        $this->assertSame($data, $this->documentStore->get($this->mongoCollection, $id));
        $this->assertFalse($this->documentStore->has($this->mongoCollection, 'baz'));


        $reflClass = new \ReflectionClass($this->documentStore);
        $saveMethod = $reflClass->getMethod('remove');
        $saveMethod->setAccessible(true);
        $saveMethod->invoke($this->documentStore, $this->mongoCollection, $id);

        $this->assertFalse($this->documentStore->has($this->mongoCollection, $id));
        $this->assertNull($this->documentStore->get($this->mongoCollection, $id));
    }

    public function testInitiIsolationFromCursor()
    {
        $data = ['_id' => new \MongoId, 'test' => 'test'];

        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
        ->disableOriginalConstructor()
        ->getMock();

        //Emulate foreach behavior
        $mongoCursorMock->expects($this->at(0))
        ->method('rewind');

        $mongoCursorMock->expects($this->at(1))
        ->method('valid')
        ->will($this->returnValue(true));

        $mongoCursorMock->expects($this->at(2))
        ->method('current')
        ->will($this->returnValue($data));

        $mongoCursorMock->expects($this->at(3))
        ->method('key')
        ->will($this->returnValue(0));

        $mongoCursorMock->expects($this->at(4))
        ->method('next')
        ->will($this->returnValue(false));

        $this->assertSame(
            [$data],
            $this->documentStore->initIsolationFromCursor($this->mongoCollection, $mongoCursorMock)
        );
        $this->assertHasDocumentCache($data['_id'], $data);
    }

    public function testInitIsolationFromCursorShouldThrowDocumentModifiedException()
    {
        $data = ['_id' => new \MongoId, 'test' => 'test'];

        $mongoCursorMock = $this->getMockBuilder('\MongoCursor')
        ->disableOriginalConstructor()
        ->getMock();

        //Emulate foreach behavior
        $mongoCursorMock->expects($this->at(0))
        ->method('rewind');

        $mongoCursorMock->expects($this->at(1))
        ->method('valid')
        ->will($this->returnValue(true));

        $mongoCursorMock->expects($this->at(2))
        ->method('current')
        ->will($this->returnValue($data));

        $reflClass = new \ReflectionClass($this->documentStore);
        $saveMethod = $reflClass->getMethod('save');
        $saveMethod->setAccessible(true);
        $saveMethod->invoke($this->documentStore, $this->mongoCollection, $data['_id'], $data + ['foo' => 'bar']);

        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $this->assertSame(
            [$data],
            $this->documentStore->initIsolationFromCursor($this->mongoCollection, $mongoCursorMock)
        );
    }

    public function testIsolatedUpsert()
    {
        $testData = ['test' => 'test'];
        $options  = ['w' => 1];

        //Test insert
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('insert')
            ->with($this->equalTo($testData), $this->equalTo($options))
            ->will($this->returnValue(['ok' => true, 'n' => 0])); // MongoDB returns 0 on insert operation


        $this->assertEquals(
            1,
            $this->documentStore->isolatedUpsert($this->mongoCollection, $testData, $options)
        );
        $this->assertInstanceOf('\MongoId', $testData['_id']);
        $this->assertHasDocumentCache($testData['_id'], $testData);

        //Test update
        $testData['update'] = 'foo';
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('update')
            ->with(
                $this->equalTo($this->documentStore->get($this->mongoCollection, $testData['_id'])),
                $this->equalTo($testData),
                $this->equalTo(array_merge($options, ['multi' => false, 'upsert' => false]))
            )
            ->will($this->returnValue(['ok' => true, 'n' => 1, 'updatedExisting' => true]));

        $this->assertEquals(1, $this->documentStore->isolatedUpsert($this->mongoCollection, $testData, $options));
    }

    public function testIsolatedUpsertShouldThrowDocumentModifiedException()
    {
        $testData = ['test' => 'test'];
        $options  = ['w' => 1];

        //ensure document
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('insert')
            ->with($this->equalTo($testData), $this->equalTo($options))
            ->will($this->returnValue(['ok' => true, 'n' => 0])); // MongoDB returns 0 on insert operation


        $this->assertEquals(
            1,
            $this->documentStore->isolatedUpsert($this->mongoCollection, $testData, $options)
        );

        //Test update with document modified
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('update')
            ->with(
                $this->equalTo($this->documentStore->get($this->mongoCollection, $testData['_id'])),
                $this->equalTo($testData),
                $this->equalTo(array_merge($options, ['multi' => false, 'upsert' => false]))
            )
            ->will($this->returnValue(['ok' => true, 'n' => 0, 'updatedExisting' => false]));

        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $this->assertEquals(1, $this->documentStore->isolatedUpsert($this->mongoCollection, $testData, $options));
    }

    public function testIsolatedUpsertShouldThrowDocumentModifiedExceptionWhenDuplicateKey()
    {
        $testData = ['test' => 'test', '_id' => 'foo'];
        $options  = ['w' => 1];

        $dupKeyEx = new \MongoCursorException('E11000 duplicate key error', 11000);

        //simulate duplicate key error
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('insert')
            ->with($this->equalTo($testData), $this->equalTo($options))
            ->will($this->throwException($dupKeyEx));


        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $this->documentStore->isolatedUpsert($this->mongoCollection, $testData, $options);
    }

    public function testIsolatedRemove()
    {
        $testData = ['test' => 'test'];
        $options  = ['w' => 1];

        //ensure document
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('insert')
            ->with($this->equalTo($testData), $this->equalTo($options))
            ->will($this->returnValue(['ok' => true, 'n' => 0])); // MongoDB returns 0 on insert operation

        $this->assertEquals(
            1,
            $this->documentStore->isolatedUpsert($this->mongoCollection, $testData, $options)
        );

        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('remove')
            ->with($this->equalTo($testData), $this->equalTo(['justOne' => true] + $options))
            ->will($this->returnValue(['ok' => true, 'n' => 1]));


        $this->assertEquals(
            1,
            $this->documentStore->isolatedRemove($this->mongoCollection, $testData['_id'], $options)
        );


        $this->setExpectedException('\Matryoshka\Model\Exception\RuntimeException');
        $this->assertEquals(
            1,
            $this->documentStore->isolatedRemove($this->mongoCollection, $testData['_id'], $options)
        );
    }

    public function testIsolatedRemoveShouldThrowDocumentModifiedException()
    {
        $testData = ['test' => 'test'];
        $options  = ['w' => 1];

        //ensure document
        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('insert')
            ->with($this->equalTo($testData), $this->equalTo($options))
            ->will($this->returnValue(['ok' => true, 'n' => 0])); // MongoDB returns 0 on insert operation

        $this->assertEquals(
            1,
            $this->documentStore->isolatedUpsert($this->mongoCollection, $testData, $options)
        );

        $this->mongoCollectionMock->expects($this->atLeastOnce())
            ->method('remove')
            ->with($this->equalTo($testData))
            ->will($this->returnValue(['ok' => true, 'n' => 0]));

        $this->setExpectedException('\Matryoshka\Model\Wrapper\Mongo\Exception\DocumentModifiedException');
        $this->assertEquals(
            1,
            $this->documentStore->isolatedRemove($this->mongoCollection, $testData['_id'], $options)
        );
    }
}
