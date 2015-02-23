# Matryoshka wrapper for MongoDB

[![Latest Stable Version](https://img.shields.io/packagist/v/matryoshka-model/mongo-wrapper.svg?style=flat-square)](https://packagist.org/packages/matryoshka-model/mongo-wrapper)
[![Total Downloads](https://img.shields.io/packagist/dt/matryoshka-model/mongo-wrapper.svg?style=flat-square)](https://packagist.org/packages/matryoshka-model/mongo-wrapper)

| Master  | Develop |
|:-------------:|:-------------:|
| [![Build Status](https://img.shields.io/travis/matryoshka-model/mongo-wrapper/master.svg?style=flat-square)](https://travis-ci.org/matryoshka-model/mongo-wrapper) | [![Build Status](https://img.shields.io/travis/matryoshka-model/mongo-wrapper/develop.svg?style=flat-square)](https://travis-ci.org/matryoshka-model/mongo-wrapper) |
| [![Coveralls branch](https://img.shields.io/coveralls/matryoshka-model/mongo-wrapper/master.svg?style=flat-square)](https://coveralls.io/r/matryoshka-model/mongo-wrapper?branch=master) | [![Coveralls branch](https://img.shields.io/coveralls/matryoshka-model/mongo-wrapper/develop.svg?style=flat-square)](https://coveralls.io/r/matryoshka-model/mongo-wrapper?branch=develop) |

---

## Installation

Install it using [composer](http://getcomposer.org).

Add the following to your `composer.json` file:

```
"require": {
    "php": ">=5.4",
    "matryoshka-model/mongo-wrapper": "~0.6.0"
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

## Components

- `Matryoshka\Model\Wrapper\Mongo\Criteria` directory contains the aforementioned `ActiveRecordCriteria` matryoshka criteria.

- `Matryoshka\Model\Wrapper\Mongo\Hydrator` directory contains

    - `ClassMethods`, an hydrator that can be used with matryoshka objects when you have MongoDB collections as datagateways
    
    - `NamingStrategy\DefaultNamingStrategy` and `NamingStrategy\UnderscoreNamingStrategy`, two strategies that can be overridden to setup the naming rules map of your fields. By default, both convert `_id` to `id`.
    
    - `Strategy\*`, some common strategies for MongoDB.
    

- `Matryoshka\Model\Wrapper\Mongo\Paginator` directory contains the aforementioned `MongoPaginatorAdapter` adapter.

- `Matryoshka\Model\Wrapper\Mongo\ResultSet` contains the aforementioned `HydratingResultSet` which extends matryoshka's `HydratingResultSet` to make the `MongoCursor` counting functionality working properly.

- `Matryoshka\Model\Wrapper\Mongo\Service` contains abstract service factories generally aimed at instantiation of `\MongoCollection` and `\MongoDb` objects. Use `mongocollection` and `mongodb` configuration nodes to respectively setup them (see [above](#configuration)).

---

[![Analytics](https://ga-beacon.appspot.com/UA-49655829-1/matryoshka-model/mongo-wrapper)](https://github.com/igrigorik/ga-beacon)
