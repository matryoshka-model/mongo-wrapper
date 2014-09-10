<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace MatryoshkaModelWrapperMongoTest\Criteria\TestAsset;

class MongoCollectionSubject {


    protected $observer;

    public function __construct($observer)
    {
        $this->observer = $observer;
    }

    /**
	 * Saves an object to this collection
	 * @link http://www.php.net/manual/en/mongocollection.save.php
	 * @param mixed $a Array to save.
	 * @param array $options Options for the save.
	 * @throws MongoCursorException
	 * @return mixed
	 */
    public function save(array &$a, array $options = array())
    {
        $return = $this->observer->save($a, $options);
        $a['_id'] = new \MongoId();
        return $return;
    }

}
