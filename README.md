# Matryoshka wrapper for MongoDB [![Latest Stable Version](https://poser.pugx.org/matryoshka-model/mongo-wrapper/v/stable.png)](https://packagist.org/packages/matryoshka-model/mongo-wrapper)

| Master  | Develop |
|:-------------:|:-------------:|
| [![Build Status](https://secure.travis-ci.org/matryoshka-model/mongo-wrapper.svg?branch=master)](https://travis-ci.org/matryoshka-model/mongo-wrapper)  | [![Build Status](https://secure.travis-ci.org/matryoshka-model/mongo-wrapper.svg?branch=develop)](https://travis-ci.org/matryoshka-model/mongo-wrapper)  |
| [![Coverage Status](https://coveralls.io/repos/matryoshka-model/mongo-wrapper/badge.png?branch=master)](https://coveralls.io/r/matryoshka-model/mongo-wrapper)  | [![Coverage Status](https://coveralls.io/repos/matryoshka-model/mongo-wrapper/badge.png?branch=develop)](https://coveralls.io/r/matryoshka-model/mongo-wrapper)  |

---

...

## Installation

Install it using [composer](http://getcomposer.org).

Add the following to your `composer.json` file:

```
"require": {
    "php": ">=5.4",
    "matryoshka-model/mongo-wrapper": "~0.4.0",
}
```

##### NOTES

Since **mongo-wrapper** uses `self.version` for its [matryoshka library](https://github.com/matryoshka-model/matryoshka) dependency composer will install **matryoshka** 0.4.0.

## Configuration

This library provides two abstract factories for `Zend\ServiceManager` to make MongoDb and MongoCollection available as services. In order to use them in a ZF2 application, register the provided factories through the `service_manager` configuration node:

```php
'service_manager'    => [
    'abstract_factories' => array(
        'Matryoshka\Model\Wrapper\Mongo\Service\MongoDbAbstractServiceFactory',
        'Matryoshka\Model\Wrapper\Mongo\Service\MongoCollectionAbstractServiceFactory',
    ),
],
```

Then in your configuration you can add the `mongodb` and `mongocollection` nodes and configure them as in example:

```php
'mongodb' => [
    'Application\MongoDb' => [
        'hosts' => '127.0.0.1:27017',
        'database' => 'yourDatabase'
    ],
    ...
],

'mongocollection'    => [
    'Application\DataGateway\YourCollectionName' => [
        'database'   => 'Application\MongoDb',
        'collection' => 'yourCollectionName'
    ],
    ...
],
```

## Versioning

This library is versioned in parallel with matryoshka library (which follows [semantic versioning](https://github.com/matryoshka-model/matryoshka)).

##### NOTES

On the **master branch** you will find **major releases** while the **next minor release** is on the **develop branch** (see `extras` field in the `composer.json` file for further details, i.e. aliases).

---

[![Analytics](https://ga-beacon.appspot.com/UA-49655829-1/matryoshka-model/mongo-wrapper)](https://github.com/igrigorik/ga-beacon)
