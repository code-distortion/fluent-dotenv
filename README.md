# Fluent dotenv

[![Latest Version on Packagist](https://img.shields.io/packagist/v/code-distortion/fluent-dotenv.svg?style=flat-square)](https://packagist.org/packages/code-distortion/fluent-dotenv)
![PHP Version](https://img.shields.io/badge/PHP-7.0%20to%208.3-blue?style=flat-square)
![vlucas/phpdotenv](https://img.shields.io/badge/vlucas%2Fphpdotenv-1+-blue?style=flat-square)
![symfony/dotenv](https://img.shields.io/badge/symfony%2Fdotenv-3.3+-blue?style=flat-square)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/code-distortion/fluent-dotenv/run-tests.yml?branch=master&style=flat-square)](https://github.com/code-distortion/fluent-dotenv/actions)
[![Buy The World a Tree](https://img.shields.io/badge/treeware-%F0%9F%8C%B3-lightgreen?style=flat-square)](https://plant.treeware.earth/code-distortion/fluent-dotenv)
[![Contributor Covenant](https://img.shields.io/badge/contributor%20covenant-v2.0%20adopted-ff69b4.svg?style=flat-square)](CODE_OF_CONDUCT.md)

***code-distortion/fluent-dotenv*** is a wrapper with a fluent interface for new and old versions of [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv), allowing you to easily read values from .env files.



## Table of Contents

* [Introduction](#introduction)
* [Overview](#overview)
* [Installation](#installation)
* [Usage](#usage)
    * [Reading values from .env files](#reading-values-from-env-files)
    * [Filtering](#filtering)
    * [Validation](#validation)
    * [Casting values](#casting-values)
    * [Populating $_ENV and $_SERVER superglobals](#populating-_env-and-_server-superglobals)
    * [Putenv and getenv](#putenv-and-getenv)
    * [Calling order](#calling-order)
* [Other dotenv reader packages - symfony/dotenv](#other-dotenv-reader-packages---symfonydotenv)
* [Testing](#testing)
* [Changelog](#changelog)
    * [SemVer](#semver)
* [Treeware](#treeware)
* [Contributing](#contributing)
    * [Code of Conduct](#code-of-conduct)
    * [Security](#security)
* [Credits](#credits)
* [License](#license)



## Introduction

.env files are an important tool, allowing for customisation of projects between environments.

Sometimes when you're building a package you need it to work with multiple versions of vlucas/phpdotenv so your own package can provide coverage. This was the original motivation behind this package.

> First released in 2013, [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) by Vance Lucas and Graham Campbell is the most used PHP solution for loading values from .env files. Please have a look at their page for a more detailed description of what .env files are and why they're used.



## Overview

This package provides a new fluent interface for vlucas/phpdotenv features, including the ability to:

- Specify values to explicitly *pick* or *ignore*,
- Specify values that are *required*,
- Perform validation - make sure values are *not empty*, are *integers*, *booleans*, are *limited to a specific set of values*, match a *regex* or validate via a *callback*,
- Populate *$_ENV* and *$_SERVER* values if you like, choosing whether to override values that already exist or not.



## Installation

Install the package via composer:

``` bash
composer require code-distortion/fluent-dotenv
```



## Usage

### Reading values from .env files

``` php
use CodeDistortion\FluentDotEnv\FluentDotEnv;

// simply load the values from one or more .env files
$fDotEnv = FluentDotEnv::new()->load('path/to/.env');
$fDotEnv = FluentDotEnv::new()->load(['path/to/.env', 'path/to/another.env']);

// don't throw an exception if the file doesn't exist
$fDotEnv = FluentDotEnv::new()->safeLoad('path/to/missing.env');

// get all the values as an associative array
$allValues = $fDotEnv->all();

// get a single value
$host = $fDotEnv->get('HOST');

// get several values (returned as an associative array)
$dbCredentials = $fDotEnv->get(['HOST', 'PORT', 'USERNAME', 'PASSWORD']);
```

> ***NOTE:*** Over time, vlucas/phpdotenv has improved the way it reads values from .env files (eg. multi-line variables). The underlying version you have will determine how values in .env files are interpreted.



### Filtering

If you only want to load specific keys you can specify them, others from your .env file will be ignored:

``` php
$fDotEnv = FluentDotEnv::new()
    ->pick('MY_KEY1')              // add a key to pick
    ->pick('MY_KEY2')              // values can be passed individually
    ->pick(['MY_KEY3', 'MY_KEY4']) // or multiple as an array
    ->load('path/to/.env');
```

Conversely, particular keys can be ignored:

``` php
$fDotEnv = FluentDotEnv::new()
    ->ignore('MY_KEY1')              // add a key to ignore
    ->ignore('MY_KEY2')              // values can be passed individually
    ->ignore(['MY_KEY3', 'MY_KEY4']) // or multiple as an array
    ->load('path/to/.env');
```



### Validation

Validation can be applied to the values in an .env file. A `CodeDistortion\FluentDotEnv\Exceptions\ValidationException` exception will be thrown when a value fails validation:

``` php
$fDotEnv = FluentDotEnv::new()

    // make sure these keys exist
    ->required('MY_KEY')
    ->required(['MY_KEY1', 'MY_KEY2'])

    // when these keys exist, make sure they aren't empty
    ->notEmpty('MY_KEY')
    ->notEmpty(['MY_KEY1', 'MY_KEY2'])

    // when these keys exist, make sure they are integer strings
    ->integer('MY_KEY')
    ->integer(['MY_KEY1', 'MY_KEY2'])

    // when these keys exist, make sure they are boolean strings
    // ie. "true", "false", "On", "Off", "Yes", "No", "1" and "0"
    ->boolean('MY_KEY')
    ->boolean(['MY_KEY1', 'MY_KEY2'])

    // when a key exists, make sure its value is in a predefined list
    ->allowedValues('MY_KEY', ['value-1', 'value-2'])
    // allow predefined values for a multiple keys
    ->allowedValues(['MY_KEY1', 'MY_KEY2'], ['value-1', 'value-2'])
    // different predefined values can be specified for different keys in one go
    ->allowedValues([
        'MY_KEY1' => ['value-1', 'value-2'],
        'MY_KEY2' => ['value-a', 'value-b'],
    ])

    // when a key exists, make sure its value matches a regular expression
    ->regex('MY_KEY', '/^[0-9]+\.[0-9]{2}$/')
    // the same regex can be applied to multiple keys
    ->regex(['MY_KEY1', 'MY_KEY2'], '/^[0-9]+\.[0-9]{2}$/')
    // different regexes can be applied to different keys in one go
    ->regex([
        'MY_KEY1' => '/^[0-9]+\.[0-9]{2}$/',
        'MY_KEY2' => '/^[a-z]+$/'
    ])

    // when a key exists, validate it's value via a callback
    ->callback('MY_KEY', $callback)
    // the same callback can be applied to multiple keys
    ->callback(['MY_KEY1', 'MY_KEY2'], $callback)
    // different callbacks can be applied to different keys in one go
    ->callback([
        'MY_KEY1' => $callback1,
        'MY_KEY2' => $callback2,
    ])
    // validate *all* values via a callback
    ->callback(function (string $key, $value) {
        return true; // or false
    })

    // the validation is applied when load is called
    ->load('path/to/.env');
```



### Casting values

Values retrieved using `get(…)` or `all(…)` are returned as strings.

`castBoolean(…)` and `castInteger(…)` are available for convenience to retrieve values as booleans or integers (`null` will be returned when the values aren't "booleans" or integers).

``` php
$fDotEnv = FluentDotEnv::new()->load('path/to/.env');

// cast to a boolean when the value is one of:
// "true", "false", "On", "Off", "Yes", "No", "1" and "0"
$boolean = $fDotEnv->castBoolean('MY_KEY');
$booleans = $fDotEnv->castBoolean(['MY_KEY1', 'MY_KEY2']);

// cast to an integer, including negative numbers
$integer = $fDotEnv->castInteger('MY_KEY');
$integers = $fDotEnv->castInteger(['MY_KEY1', 'MY_KEY2']);
```



### Populating $_ENV and $_SERVER superglobals

The $_ENV and $_SERVER superglobals can be populated with the imported values:

``` php
$fDotEnv = FluentDotEnv::new()

    // add the loaded values to $_ENV
    ->populateEnv()
    // add values to $_ENV and override values that already exist
    ->populateEnv(true)

    // add the loaded values to $_SERVER
    ->populateServer()
    // add values to $_SERVER and override values that already exist
    ->populateServer(true)

    // the values are added when load is called
    ->load('path/to/.env');
```



### Putenv and getenv

`putenv(…)` and `getenv(…)` are not thread-safe. For this reason this functionality is not included in this package.



### Calling order

The methods above can be called *before* or *after* loading values from .env files. eg.

``` php
$fDotEnv = FluentDotEnv::new()
    ->load('path/to/.env') // loaded at the beginning
    ->pick(['MY_KEY1', 'MY_KEY2'])
    ->integer('MY_KEY1')
    ->boolean('MY_KEY2')
    ->populateEnv();

$fDotEnv = FluentDotEnv::new()
    ->pick(['MY_KEY1', 'MY_KEY2']) // deferred
    ->integer('MY_KEY1') // deferred
    ->boolean('MY_KEY2') // deferred
    ->populateEnv() // deferred
    ->load('path/to/.env'); // loaded at the end
```

> ***NOTE:*** When you call `populateEnv()` or `populateServer()` *after* `load()` has been called, the $_ENV and $_SERVER arrays respectively will be updated straight away.



## Other dotenv reader packages - symfony/dotenv

[symfony/dotenv](https://github.com/symfony/dotenv) was first released in 2017 and is another commonly used dotenv reader.

You can use symfony/dotenv to read .env files by calling `useSymfonyDotEnv()` before calling `load()`. eg.

``` php
$fDotEnv = FluentDotEnv::new()
    ->useSymfonyDotEnv()
    ->load('path/to/.env')
```

> ***NOTE:*** `symfony/dotenv` must be included in your project as a dependency for this to work.

``` bash
composer require symfony/dotenv
```



## Testing

``` bash
composer test
```



## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.



### SemVer

This library uses [SemVer 2.0.0](https://semver.org/) versioning. This means that changes to `X` indicate a breaking change: `0.0.X`, `0.X.y`, `X.y.z`. When this library changes to version 1.0.0, 2.0.0 and so forth it doesn't indicate that it's necessarily a notable release, it simply indicates that the changes were breaking.



## Treeware

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/code-distortion/fluent-dotenv) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.



## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.



### Code of Conduct

Please see [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.



### Security

If you discover any security related issues, please email tim@code-distortion.net instead of using the issue tracker.



## Credits

- [Tim Chandler](https://github.com/code-distortion)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
