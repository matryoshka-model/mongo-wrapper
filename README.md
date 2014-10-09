# Matryoshka wrapper for MongoDB

[![Latest Stable Version](https://poser.pugx.org/matryoshka-model/mongo-wrapper/v/stable.png)](https://packagist.org/packages/matryoshka-model/mongo-wrapper)&nbsp;[![Dependency Status](https://www.versioneye.com/user/projects/5432e06f84981f0f8800004f/badge.svg)](https://www.versioneye.com/user/projects/5432e06f84981f0f8800004f)&nbsp;[![Total Downloads](https://poser.pugx.org/matryoshka-model/mongo-wrapper/downloads.svg)](https://packagist.org/packages/matryoshka-model/mongo-wrapper)

| Master  | Develop |
|:-------------:|:-------------:|
| [![Build Status](https://secure.travis-ci.org/matryoshka-model/mongo-wrapper.svg?branch=master)](https://travis-ci.org/matryoshka-model/mongo-wrapper)  | [![Build Status](https://secure.travis-ci.org/matryoshka-model/mongo-wrapper.svg?branch=develop)](https://travis-ci.org/matryoshka-model/mongo-wrapper)  |
| [![Coverage Status](https://coveralls.io/repos/matryoshka-model/mongo-wrapper/badge.png?branch=master)](https://coveralls.io/r/matryoshka-model/mongo-wrapper)  | [![Coverage Status](https://coveralls.io/repos/matryoshka-model/mongo-wrapper/badge.png?branch=develop)](https://coveralls.io/r/matryoshka-model/mongo-wrapper)  |

---

## Installation

Install it using [composer](http://getcomposer.org).

Add the following to your `composer.json` file:

```
"require": {
    "php": ">=5.4",
    "matryoshka-model/mongo-wrapper": "~0.5.0"
}
```

## Configuration

This library provides two abstract factories for `Zend\ServiceManager` to make MongoDb and MongoCollection available as services. In order to use them in a ZF2 application, register the provided factories through the `service_manager` configuration node:

```php
'service_manager'    => [
    'abstract_factories' => [
        'Matryoshka\Model\Wrapper\Mongo\Service\MongoDbAbstractServiceFactory',
        'Matryoshka\Model\Wrapper\Mongo\Service\MongoCollectionAbstractServiceFactory',
    ],
],
```

Then in your configuration you can add the `mongodb` and `mongocollection` nodes and configure them as in example:

```php
'mongodb' => [
    'Application\MongoDb\YourDatabaseName' => [
        'hosts' => '127.0.0.1:27017',
        'database' => 'yourDatabaseName'
    ],
    ...
],

'mongocollection'    => [
    'Application\DataGateway\YourCollectionName' => [
        'database'   => 'Application\MongoDb\YourDatabaseName',
        'collection' => 'yourCollectionName'
    ],
    ...
],
```

## Usage

This wrapper provides extensions and default implementations for using `MongoCollection` as a datagateway.

Main concepts:

1. Inject a `Matryoshka\Model\Wrapper\Mongo\Criteria\ActiveRecordCriteria` instance into your `Matryoshka\Model\Object\AbstractActiveRecord` objects

    This way the matryoshka [Active Record](http://www.martinfowler.com/eaaCatalog/activeRecord.html) implementation will work with your MongoDB collections

2. `Matryoshka\Model\Wrapper\Mongo\Paginator`

    `MongoPaginatorAdapter` is a paginator adapter that can be used within paginable criterias

3. `Matryoshka\Model\Wrapper\Mongo\ResultSet`

    `HydratingResultSet` makes the counting functionality working correctly with `MongoCursor` datasources

##### NOTES

It's important to always use the `HydratingResultSet` class included in this package because [`MongoCursor`](http://php.net/manual/en/class.mongocursor.php) does not implement the [`Countable`](http://php.net/manual/en/class.countable.php) and [`MongoCursor::count()`](http://php.net/manual/en/mongocursor.count.php) must be called passing `true` as parameter.

### Components

- `Matryoshka\Model\Wrapper\Mongo\Criteria` contains:

    The aforementioned `ActiveRecordCriteria` matryoshka criteria

- `Matryoshka\Model\Wrapper\Mongo\Hydrator` contains:

    - `ClassMethods`, an hydrator that can be used with matryoshka objects when you have MongoDB collections as datagateways
    - `NamingStrategy\IdNameStrategy`, a strategy that can be overridden to setup the naming rules map of your fields
    - `Strategy\*`, some common strategies for MongoDB

- `Matryoshka\Model\Wrapper\Mongo\Paginator` contains:

    The aforementioned `MongoPaginatorAdapter`

- `Matryoshka\Model\Wrapper\Mongo\ResultSet` contains:

    The aforementioned `HydratingResultSet` which extends matryoshka's `HydratingResultSet` to make the `MongoCursor`s counting functionality working properly

- `Matryoshka\Model\Wrapper\Mongo\Service` contains:

    Abstract service factories generally aimed at instantiation of `\MongoCollection`s and `\MongoDb`s.
    Use `mongocollection` and `mongodb` configuration node to respectively setup them.

---

[![Analytics](https://ga-beacon.appspot.com/UA-49655829-1/matryoshka-model/mongo-wrapper)](https://github.com/igrigorik/ga-beacon)
