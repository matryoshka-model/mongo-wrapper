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

/**
 * Class DocumentStoreAwareTraitTest
 */
class DocumentStoreAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated\DocumentStoreAwareTrait
     */
    protected $traitObject;

    protected $documentStore;

    public function setUp()
    {
        $this->traitObject = $this->getObjectForTrait(
            'Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated\DocumentStoreAwareTrait'
        );
        $this->documentStore = DocumentStore::getSharedInstance();
    }

    public function testSetModel()
    {
        $store = clone $this->documentStore;
        $this->assertSame($this->traitObject, $this->traitObject->setDocumentStore($store));
        $this->assertAttributeEquals($store, 'documentStore', $this->traitObject);
    }

    public function testGetModel()
    {
        $this->assertSame($this->documentStore, $this->traitObject->getDocumentStore());

        $store = clone $this->documentStore;
        $this->traitObject->setDocumentStore($store);
        $this->assertEquals($store, $this->traitObject->getDocumentStore());
    }
}
