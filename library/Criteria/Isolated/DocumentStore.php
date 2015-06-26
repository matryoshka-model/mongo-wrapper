<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated;

use MongoCollection;
use MongoCursor;
use MongoCursorException;
use ArrayObject;
use SplObjectStorage;
use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\Exception\RuntimeException;
use Matryoshka\Model\Wrapper\Mongo\Exception;
use Matryoshka\Model\Wrapper\Mongo\Criteria\HandleResultTrait;

/**
 * Class DocumentStore
 *
 * DocumentStore mantains a local copy of the fetched documents.
 * It uses the entire document to perform queries in the update() and remove() operations,
 * applying the changes only if the fields have not changed in the collection
 * since the find query (otherwise a {@link DocumentModifiedException} will be thrown).
 *
 * In order to make the isolation pattern working properly
 * all the involved objects MUST share the same DocumentStore instance during their whole lifecycle.
 *
 * More at:
 * @link http://docs.mongodb.org/manual/tutorial/isolate-sequence-of-operations
 */
class DocumentStore
{

    use HandleResultTrait;

    /**
     * @var DocumentStore
     */
    protected static $instance;

    /**
     * @var SplObjectStorage
     */
    protected $splObjectStorage;

    /**
     * Ctor
     */
    public function __construct()
    {
        $this->splObjectStorage = new SplObjectStorage();
    }

    /**
     * Retrieve shared instance
     *
     * @return DocumentStore
     */
    public static function getSharedInstance()
    {
        if (null === static::$instance) {
            static::setSharedInstance(new static());
        }
        return static::$instance;
    }

    /**
     * Set the shared singleton to a specific DocumentStore instance
     *
     * @param DocumentStore $instance
     * @return void
     */
    public static function setSharedInstance(DocumentStore $instance)
    {
        static::$instance = $instance;
    }

    /**
     * Is a shared instance defined?
     *
     * @return bool
     */
    public static function hasSharedInstance()
    {
        return (static::$instance instanceof DocumentStore);
    }

    /**
     * Reset the shared instance
     *
     * @return void
     */
    public static function resetSharedInstance()
    {
        static::$instance = null;
    }

    /**
     * Check whether the given data gateway exists in the current store
     *
     * @param MongoCollection $dataGateway
     * @param $id
     * @return bool
     */
    public function has(MongoCollection $dataGateway, $id)
    {
        $id = (string) $id;

        if (!$this->splObjectStorage->contains($dataGateway)) {
            return false;
        }

        if (isset($this->splObjectStorage[$dataGateway][$id])) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the given data gateway from the store
     *
     * @param MongoCollection $dataGateway
     * @param $id
     * @return mixed
     */
    public function get(MongoCollection $dataGateway, $id)
    {
        $id = (string) $id;

        if ($this->has($dataGateway, $id)) {
            return $this->splObjectStorage[$dataGateway][$id];
        }

        // FIXME: missing return
    }

    /**
     * @param MongoCollection $dataGateway
     * @param $id
     * @param array $data
     */
    protected function save(MongoCollection $dataGateway, $id, array $data)
    {
        $id = (string) $id;

        if (!$this->splObjectStorage->contains($dataGateway)) {
            $this->splObjectStorage[$dataGateway] = new ArrayObject();
        }

        $this->splObjectStorage[$dataGateway][$id] = $data;
    }

    /**
     * @param MongoCollection $dataGateway
     * @param $id
     */
    protected function remove(MongoCollection $dataGateway, $id)
    {
        $id = (string) $id;

        if ($this->has($dataGateway, $id)) {
            unset($this->splObjectStorage[$dataGateway][$id]);
        }
    }

    /**
     * @param MongoCollection $dataGateway
     * @param MongoCursor $cursor
     * @return array
     */
    public function initIsolationFromCursor(MongoCollection $dataGateway, MongoCursor $cursor)
    {
        $return = [];
        foreach ($cursor as $document) {
            $id = $document['_id']; // FIXME: check id presence
            $localDocument = $this->get($dataGateway, $id);
            if ($localDocument && $document != $localDocument) {
                throw new Exception\DocumentModifiedException(sprintf(
                    'The local copy of the document "%s" no longer reflects the current state of the document in the database',
                    $id
                ));
            }
            $this->save($dataGateway, $id, $document);
            $return[] = $document;
        }
        return $return;
    }

    /**
     * @param MongoCollection $dataGateway
     * @param array $data
     * @param array $options
     * @return array|bool|int|null
     */
    public function isolatedUpsert(MongoCollection $dataGateway, array &$data, array $options = [])
    {
        // NOTE: we must ensure that modifiers in $data are not allowed

        if (!isset($data['_id']) || !$this->has($dataGateway, $data['_id'])) {
            // Insert
            $tmp = $data; // passing a referenced variable to insert will fail
                          // in update the content
            try {
                $result = $dataGateway->insert($tmp, $options); // modifiers are not allowed with insert
            } catch (MongoCursorException $e) {
                if ($e->getCode() == 11000) {
                    $e = new Exception\DocumentModifiedException(
                        sprintf(
                            'Cannot insert the local copy of the new document "%s" because another ' .
                            ' document with the same ID already exists in the database',
                            $data['_id']
                        ),
                        11000,
                        $e
                    );
                }
                throw $e;
            }
            $result = $this->handleResult($result);
            $data = $tmp;
        } else {
            // Update
            $oldDocumentData = $this->get($dataGateway, $data['_id']);
            $result = $dataGateway->update(
                $oldDocumentData,
                $data, // modifiers and non-modifiers cannot be mixed,
                // the _id presence ensure at least one non-modifiers
                array_merge($options, ['multi' => false, 'upsert' => false])
            );
            $result = $this->handleResult($result);

            if ($result != 1) {
                throw new Exception\DocumentModifiedException(sprintf(
                    'The local copy of the document "%s" no longer reflects the current state of the document in the database',
                    $data['_id']
                ));
            }
        }

        $this->save($dataGateway, $data['_id'], $data);
        return $result;
    }

    /**
     * @param MongoCollection $dataGateway
     * @param $id
     * @param array $options
     * @return array|bool|int|null
     */
    public function isolatedRemove(MongoCollection $dataGateway, $id, array $options = [])
    {
        if (!$this->has($dataGateway, $id)) {
            throw new RuntimeException(sprintf('No local copy found for the document "%s"', $id));
        }

        $result = $dataGateway->remove($this->get($dataGateway, $id), $options);
        $result = $this->handleResult($result, true);

        if ($result != 1) {
            throw new Exception\DocumentModifiedException(sprintf(
                'The local copy of the document "%s" no longer reflects the current state of the document in the database',
                $id
            ));
        }

        $this->remove($dataGateway, $id);
        return $result;
    }
}
