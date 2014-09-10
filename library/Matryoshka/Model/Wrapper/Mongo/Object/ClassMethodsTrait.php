<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2014, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Matryoshka\Model\Wrapper\Mongo\Object;

use Zend\Stdlib\Hydrator\HydratorAwareTrait;
use Matryoshka\Model\Wrapper\Mongo\Hydrator\ClassMethods;

/**
 * Trait ClassMethodsTrait
 */
trait ClassMethodsTrait
{
    use HydratorAwareTrait;

    /**
     * @var string
     */
    protected $_id;

    /**
     * Get Id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set Id
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $this->hydrator = new ClassMethods();
        }

        return $this->hydrator;
    }

    /**
     * Get
     *
     * @param $name
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __get($name)
    {
        throw new \InvalidArgumentException('Object using ClassMethods hydrator cannot have public property');
    }

    /**
     * Set
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __set($name, $value)
    {
        throw new \InvalidArgumentException('Object using ClassMethods hydrator cannot have public property');
    }

    /**
     * Unset
     *
     * @param string $name
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __unset($name)
    {
        throw new \InvalidArgumentException('Object using ClassMethods hydrator cannot have public property');
    }
}
