<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated;

/**
 * Class HandleResultTrait
 */
trait DocumentStoreAwareTrait
{
    /**
     * @var DocumentStore
     */
    protected $documentStore;

    /**
     * @return DocumentStore
     */
    public function getDocumentStore()
    {
        if (null === $this->documentStore) {
            $this->setDocumentStore(DocumentStore::getSharedInstance());
        }

        return $this->documentStore;
    }

    /**
     * @param DocumentStore $documentStore
     * @return $this
     */
    public function setDocumentStore(DocumentStore $documentStore)
    {
        $this->documentStore = $documentStore;
        return $this;
    }
}
