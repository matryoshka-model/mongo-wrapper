<?php
/**
 * MongoDB matryoshka wrapper
 *
 * @link        https://github.com/matryoshka-model/mongo-wrapper
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Model\Wrapper\Mongo\Exception;

/**
 * Class DocumentModifiedException
 *
 * Exception thrown when an isolated operation breaks because changes have been detected during its execution.
 * @see DocumentStore
 */
class DocumentModifiedException extends MongoResultException implements ExceptionInterface
{
}
