<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaModelWrapperMongoTest\TestAsset;
/**
 * Class MongoCollectionMockProxy
 *
 * Proxy for \MongoCollection's mock in order to simulate the special behavior of insert() and save().
 *
 * MongoCollection::insert() and save() first parameter report the following specifications:
 * if the parameter does not have an _id key or property, a new MongoId instance will be created and assigned to it.
 * This special behavior does not mean that the parameter is passed by reference.
 *
 * The behavior can be simulated using parameter passed by reference but PHPUnit mock implementation doesn't allow that.
 * MongoCollectionMockProxy bypasses the issue changing the original method signature,
 * proxing the method call to the mock and finally adding the _id.
 *
 * NOTE: E_STRICT error reporting must be disabled due to incompatibile method signature with the parent class
 *
 * MongoCollection skeleton class by {@link https://github.com/localgod/PeclMongoPhpDoc}
 */
class MongoCollectionMockProxy extends \MongoCollection
{
    protected $mock;

    /**
     * Ctor
     *
     * @param
     */
    public function __construct()
    {
        //won't call parent::__construct
    }

    public function __MongoCollectionMockProxy__setMock($mock)
    {
        $this->mock = $mock;
    }

    public function __set($name, $value)
    {
        return $this->mock->{$name} = $value;
    }

    public function __get($name)
    {
        return $this->mock->{$name};
    }

    public function __call($method, array $params)
    {
        if (count($params) > 0) {
            return call_user_func_array([$this->mock, $method], $params);
        }
        return call_user_func([$this->mock, $method]);
    }

    /**
     * Saves an object to this collection
     *
     * @link http://www.php.net/manual/en/mongocollection.save.php
     * @param mixed $a
     *            Array to save.
     * @param array $options
     *            Options for the save.
     * @throws \MongoCursorException
     * @return mixed
     */
    public function save(array &$a, array $options = [])
    {
        $return = $this->mock->save($a, $options);
        if (!isset($a['_id']) || $a['_id'] === null) {
            $a['_id'] = new \MongoId();
        }
        return $return;
    }

    /**
     * Inserts a document into the collection
     *
     * @link http://php.net/manual/en/mongocollection.insert.php
     * @param mixed $a
     *            Array to save.
     * @param array $options
     *            Options for the save.
     * @throws \MongoCursorException
     * @return mixed
     */
    public function insert(array &$a, array $options = [])
    {
        $return = $this->mock->insert($a, $options);
        if (!isset($a['_id']) || $a['_id'] === null) {
            $a['_id'] = new \MongoId();
        }
        return $return;
    }

    /**
     * String representation of this collection
     *
     * @return string
     */
    public function __toString()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Returns this collections name
     *
     * @return string
     */
    public function getName()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Drops this collection
     *
     * @return array
     */
    public function drop()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Validates this collection
     *
     * @param boolean $scan_data
     *            Only validate indices, not the base collection.
     *
     * @return array
     */
    public function validate($scan_data = false)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Inserts multiple documents into this collection
     *
     * @param array $a
     *            An array of arrays.
     * @param array $options
     *            Options for the inserts.
     *
     * @return mixed If "safe" is set, returns an associative array with the
     *         status of the inserts ("ok")
     *         and any error that may have occured ("err"). Otherwise, returns
     *         TRUE if the batch insert
     *         was successfully sent, FALSE otherwise.
     */
    public function batchInsert($a, $options = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Update records based on a given criteria
     *
     * @param array $criteria
     *            Description of the objects to update.
     * @param array $newobj
     *            The object with which to update the matching records.
     * @param array $options
     *            Options for update.
     *
     * @return boolean
     */
    public function update($criteria, $newobj, $options = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Remove records from this collection
     *
     * @param array $criteria
     *            Description of records to remove.
     * @param array $options
     *            Options for remove.
     *
     * @return mixed
     */
    public function remove($criteria = array(), $options = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Querys this collection
     *
     * @param array $query
     *            The fields for which to search
     * @param array $fields
     *            Fields of the results to return.
     *            The array is in the format array('fieldname' => true,
     *            'fieldname2' => true).
     *            The _id field is always returned.
     *
     * @return MongoCursor
     */
    public function find($query = array(), $fields = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Querys this collection, returning a single element
     *
     * As opposed to MongoCollection::find(), this method will return only the
     * first
     * result from the result set, and not a MongoCursor that can be iterated
     * over.
     *
     * @param array $query
     *            The fields for which to search
     * @param array $fields
     *            Fields of the results to return.
     *            The array is in the format array('fieldname' => true,
     *            'fieldname2' => true).
     *            The _id field is always returned.
     *
     * @return array
     */
    public function findOne($query = array(), $fields = array(), $options = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Creates an index on the given field(s), or does nothing if the index
     * already exists
     *
     * @param array $keys
     *            An array of fields by which to sort the index on.
     *            Each element in the array has as key the field name,
     *            and as value either 1 for ascending sort, or -1 for descending
     *            sort.
     * @param array $options
     *            Options for the ensureIndex.
     *
     * @return boolean
     */
    public function ensureIndex($keys, $options = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Deletes an index from this collection
     *
     * @param string|array $keys
     *            Field or fields from which to delete the index.
     *
     * @return array
     */
    public function deleteIndex($keys)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Delete all indices for this collection
     *
     * @return array
     */
    public function deleteIndexes()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Returns an array of index names for this collection
     *
     * @return array
     */
    public function getIndexInfo()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Counts the number of documents in this collection
     *
     * @param array $query
     *            Associative array or object with fields to match.
     * @param integer $limit
     *            Specifies an upper limit to the number returned.
     * @param integer $skip
     *            Specifies a number of results to skip before starting the
     *            count.
     *
     * @return integer
     */
    public function count($query = array(), $limit = null, $skip = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Creates a database reference
     *
     * @param array $a
     *            Object to which to create a reference.
     *
     * @return array
     */
    public function createDBRef($a)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Fetches the document pointed to by a database reference
     *
     * @param array $ref
     *            A database reference.
     *
     * @return array
     */
    public function getDBRef($ref)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Performs an operation similar to SQL's GROUP BY command
     *
     * @param mixed $keys
     *            Fields to group by. If an array or non-code object
     *            is passed, it will be the key used to group results.
     * @param array $initial
     *            Initial value of the aggregation counter object.
     * @param \MongoCode $reduce
     *            A function that takes two arguments (the current
     *            document and the aggregation to this point) and does the
     *            aggregation.
     * @param array $options
     *            Optional parameters to the group command
     *
     * @return array
     */
    public function group($keys, $initial, $reduce, $options = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $query
     *              The query criteria to search for.
     * @param array $update
     *              The update criteria.
     * @param array $fields
     *              Optionally only return these fields.
     * @param array $options
     *              An array of options to apply, such as remove the match document from the DB and return it.
     *
     * @return array Returns the original document, or the modified document when new is set
     */
    public function findAndModify($query, $update = array(), $fields = array(), $options = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
