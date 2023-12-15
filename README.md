# Fluent dotenv

[![Latest Version on Packagist](https://img.shields.io/packagist/v/code-distortion/fluent-dotenv.svg?style=flat-square)](https://packagist.org/packages/code-distortion/fluent-dotenv)
![PHP Version](https://img.shields.io/badge/PHP-7.0%20to%208.3-blue?style=flat-square)
![vlucas/phpdotenv](https://img.shields.io/badge/vlucas%2Fphpdotenv-1.1+-blue?style=flat-square)
![symfony/dotenv](https://img.shields.io/badge/symfony%2Fdotenv-3.3+-blue?style=flat-square)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/code-distortion/fluent-dotenv/run-tests.yml?branch=master&style=flat-square)](https://github.com/code-distortion/fluent-dotenv/actions)
[![Buy The World a Tree](https://img.shields.io/badge/treeware-%F0%9F%8C%B3-lightgreen?style=flat-square)](https://plant.treeware.earth/code-distortion/fluent-dotenv)
[![Contributor Covenant](https://img.shields.io/badge/contributor%20covenant-v2.0%20adopted-ff69b4.svg?style=flat-square)](.github/CODE_OF_CONDUCT.md)

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

.env files are an important tool as they allow for customisation of projects between environments.

Sometimes when you're building a package, to support a wide range of other packages you need it to be able to use multiple versions of [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) or [symfony/dotenv](https://github.com/symfony/dotenv).

The motivation behind this package is to provide a way to interact with the different versions of these packages with a single interface.

> First released in 2013, [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) by Vance Lucas and Graham Campbell is the most used PHP solution for loading values from .env files. Please have a look at their page for a more detailed description of what .env files are and why they're used.

> [symfony/dotenv](https://github.com/symfony/dotenv) was first released in 2017 as an alternative that's a part of [Symfony framework](https://github.com/symfony) family.



## Overview

This package provides a new fluent interface for [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) and [symfony/dotenv](https://github.com/symfony/dotenv) features, and adds new features, including the ability to:

- Perform validation - make sure values are *not empty*, are *integers*, *booleans*, are *limited to a specific set of values*, match a *regex* or validate via a *callback*,
- Specify values to explicitly *pick* or *ignore*,
- Specify values that are *required*,
- Type casting
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

// get all the loaded values as an associative array
$allValues = $fDotEnv->all();

// get a single value
$host = $fDotEnv->get('HOST');

// get several values (returned as an associative array)
$dbCredentials = $fDotEnv->get(['HOST', 'PORT', 'USERNAME', 'PASSWORD']);
```

> ***NOTE:*** Over time, vlucas/phpdotenv and symfony/dotenv have improved the way they interpret values from .env files (e.g. multi-line variables). The underlying version of these package/s you use will determine how the .env values are interpreted.



### Filtering

If you only want to load specific keys, you can specify them. Other values from your .env file will be ignored:

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
    // i.e. "true", "false", "On", "Off", "Yes", "No", "1" and "0"
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

The $_ENV and $_SERVER superglobals can be populated with the loaded values:

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



### Putenv() and getenv()

The `putenv(…)` and `getenv(…)` functions are not thread-safe. For this reason this functionality is not included in this package.

> ***NOTE:*** symfony/dotenv [added an option to turn off the use of putenv()](https://github.com/symfony/dotenv/commit/e1f27138406a700c01d4e05e861226bb0c28b83a#diff-b73348fec7eb6dfdb482d959a985979c5bead6091837e488319d75983556f5e7R74-L74) in version 5.1.0. Before that it uses putenv() without a way to turn it off.
> 
> FluentDotEnv hides this away, leaving your environment variables the same as they were before loading. ***But***, it means that environment variables are changed temporarily during the load process which [may cause issues in a multi-threaded environment](https://github.com/symfony/symfony/discussions/49928).
>
> If you're using symfony/dotenv, you may want to consider using version 5.1.0 or higher.



### Calling order

It doesn't matter which order you call the methods above in, they can be called *before* or *after* loading values from .env files. e.g.

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

You can use symfony/dotenv to read .env files by calling `useSymfonyDotEnv()` before calling `load()`. e.g.

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

This library uses [SemVer 2.0.0](https://semver.org/) versioning. This means that changes to `X` indicate a breaking change: `0.0.X`, `0.X.y`, `X.y.z`. When this library changes to version 1.0.0, 2.0.0 and so forth, it doesn't indicate that it's necessarily a notable release, it simply indicates that the changes were breaking.



## Treeware

This package is [Treeware](https://treeware.earth). If you use it in production, then we ask that you [**buy the world a tree**](https://plant.treeware.earth/code-distortion/fluent-dotenv) to thank us for our work. By contributing to the Treeware forest you’ll be creating employment for local families and restoring wildlife habitats.



## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.



### Code of Conduct

Please see [CODE_OF_CONDUCT](.github/CODE_OF_CONDUCT.md) for details.



### Security

If you discover any security related issues, please email tim@code-distortion.net instead of using the issue tracker.



## Credits

- [Tim Chandler](https://github.com/code-distortion)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
