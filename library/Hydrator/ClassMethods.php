<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Hydrator;

use Matryoshka\Model\Hydrator\ClassMethods as MatryoshkaClassMethods;
use Matryoshka\Model\Wrapper\Mongo\Hydrator\Strategy\MongoIdStrategy;
use Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use Matryoshka\Model\Wrapper\Mongo\Hydrator\NamingStrategy\DefaultNamingStrategy;

/**
 * Class ClassMethods
 */
class ClassMethods extends MatryoshkaClassMethods
{
    /**
     * {@inheritdoc}
     */
    public function __construct($underscoreSeparatedKeys = false)
    {
        parent::__construct($underscoreSeparatedKeys);
        $this->addStrategy('_id', new MongoIdStrategy());
    }

    /**
     * {@inheritdoc}
     */
    public function setUnderscoreSeparatedKeys($underscoreSeparatedKeys)
    {
        $this->underscoreSeparatedKeys = (bool) $underscoreSeparatedKeys;
        $this->setNamingStrategy($this->underscoreSeparatedKeys ? new UnderscoreNamingStrategy : new DefaultNamingStrategy);

        return $this;
    }
}
