<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Criteria\Isolated;

use MongoCollection;
use MongoCursor;
use ArrayObject;
use SplObjectStorage;
use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\Exception\RuntimeException;
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Wrapper\Mongo\Exception;
use Matryoshka\Model\Wrapper\Mongo\Criteria\HandleResultTrait;

/**
 * Class DocumentStore
 *
 * DocumentStore mantains a local copy of fetched documents in order to use
 * the entire document as the query in the update() and remove() operations,
 * applying modification only if the fields have not changed in the collection
 * since the find query (otherwise a DocumentModifiedException will be thrown).
 *
 * In order to make the isolation pattern working properly, all objects
 * involved must share the same DocumentStore's instance throughout their
 * whole lifecycle.
 *
 *
 * @link http://docs.mongodb.org/manual/tutorial/isolate-sequence-of-operations/
 *
 */
class DocumentStore
{

    use HandleResultTrait;

    /**
     *
     * @var DocumentStore
     */
    protected static $instance;

    /**
     *
     * @var SplObjectStorage
     */
    protected $splObjectStorage;

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

    public function has(MongoCollection $dataGateway, $id)
    {
        $id = (string) $id;
        $storage = $this->splObjectStorage;

        if (!$storage->contains($dataGateway)) {
            return false;
        }

        if (isset($storage[$dataGateway][$id])) {
            return true;
        }

        return false;
    }

    public function get(MongoCollection $dataGateway, $id)
    {
        $id = (string) $id;
        if ($this->has($dataGateway, $id)) {
            return $this->splObjectStorage[$dataGateway][$id];
        }
    }

    protected function save(MongoCollection $dataGateway, $id, array $data)
    {
        $id = (string) $id;

        if (!isset($this->splObjectStorage[$dataGateway])) {
            $this->splObjectStorage[$dataGateway] = new ArrayObject();
        }

        $this->splObjectStorage[$dataGateway][$id] = $data;
    }

    protected function remove(MongoCollection $dataGateway, $id)
    {
        if ($this->has($dataGateway, $id)) {
            unset($this->splObjectStorage[$dataGateway][$id]);
        }
    }

    public function initIsolationFromCursor(MongoCollection $dataGateway, MongoCursor $cursor)
    {
        $return = [];
        foreach ($cursor as $document) {
            $id = $document['_id'];
            if ($this->has($dataGateway, $id) && $document !== $this->get($dataGateway, $id)) {
                throw new Exception\DocumentModifiedException(
                    sprintf(
                        'The local copy of the document "%s" does no more reflect the current state of the document in the database',
                        $id));
            }
            $this->save($dataGateway, $id, $document);
            $return[] = $document;
        }
        return $return;
    }

    public function isolatedUpsert(MongoCollection $dataGateway, array &$data, array $options = [])
    {
        if (!isset($data['_id']) || !$this->has($dataGateway, $data['_id'])) {
            // Insert
            $tmp = $data; // passing a referenced variable to insert will fail
                          // in update the content
            $result = $dataGateway->insert($tmp, $options);
            $result = $this->handleResult($result);
            $data = $tmp;
        } else {
            // Update
            $oldDocumentData = $this->get($dataGateway, $data['_id']);
            $result = $dataGateway->update($oldDocumentData, ['$set' => $data],
                array_merge($options, ['multi' => false,'upsert' => false]));
            $result = $this->handleResult($result);

            if ($result != 1) {
                throw new Exception\DocumentModifiedException(
                    sprintf(
                        'The local copy of the document "%s" does no more reflect the current state of the document in the database',
                        $data['_id']));
            }
        }

        $this->save($dataGateway, $data['_id'], $data);
        return $result;
    }

    public function isolatedRemove(MongoCollection $dataGateway, $id, array $options = [])
    {
        if (!$this->has($dataGateway, $id)) {
            throw new RuntimeException(
                sprintf('No local copy found for the document "%s"', $id));
        }

        $result = $dataGateway->remove($this->get($dataGateway, $id), $options);
        $result = $this->handleResult($result, true);

        if ($result != 1) {
            throw new Exception\DocumentModifiedException(
                sprintf(
                    'The local copy of the document "%s" does no more reflect the current state of the document in the database',
                    $id));
        }

        $this->remove($dataGateway, $id);
        return $result;
    }

}
