<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\Criteria\TestAsset;

/**
 * Class MongoCollectionSubject
 */
class MongoCollectionSubject
{
    protected $observer;

    /**
     * Ctor
     * @param $observer
     */
    public function __construct($observer)
    {
        $this->observer = $observer;
    }

    /**
	 * Saves an object to this collection
	 *
	 * @link http://www.php.net/manual/en/mongocollection.save.php
	 * @param mixed $a Array to save.
	 * @param array $options Options for the save.
	 * @throws \MongoCursorException
	 * @return mixed
	 */
    public function save(array &$a, array $options = [])
    {
        $return = $this->observer->save($a, $options);
        $a['_id'] = new \MongoId();
        return $return;
    }

    /**
     * Inserts a document into the collection
     *
     * @link http://php.net/manual/en/mongocollection.insert.php
     * @param mixed $a Array to save.
     * @param array $options Options for the save.
     * @throws \MongoCursorException
     * @return mixed
     */
    public function insert(array &$a, array $options = [])
    {
        $return = $this->observer->insert($a, $options);
        $a['_id'] = new \MongoId();
        return $return;
    }

    public function __call($name, $params)
    {
        return call_user_func_array([$this->observer, $name], $params);
    }

}
