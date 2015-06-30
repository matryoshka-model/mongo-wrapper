<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy;

use Zend\Stdlib\Hydrator\NamingStrategy\UnderscoreNamingStrategy as ZendUnderscoreNamingStrategy;

/**
 * Class UnderscoreNamingStrategy
 */
class UnderscoreNamingStrategy extends ZendUnderscoreNamingStrategy
{
    protected $namingMap = [
        '_id'  => 'id'
    ];

    /**
     * {@inheritdoc}
     */
    public function extract($name)
    {
        $key = array_search($name, $this->namingMap);
        return parent::extract($key ? $key : $name);
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($name)
    {
        if (isset($this->namingMap[$name])) {
            return $this->namingMap[$name];
        }
        return parent::hydrate($name);
    }
}
