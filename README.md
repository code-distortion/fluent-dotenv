# Fluent dotenv

[![Latest Version on Packagist](https://img.shields.io/packagist/v/code-distortion/fluent-dotenv.svg?style=flat-square)](https://packagist.org/packages/code-distortion/fluent-dotenv)
![PHP from Packagist](https://img.shields.io/packagist/php-v/code-distortion/fluent-dotenv?style=flat-square)
![vlucas/phpdotenv](https://img.shields.io/badge/vlucas%2Fphpdotenv-1+-blue?style=flat-square)
![symfony/dotenv](https://img.shields.io/badge/symfony%2Fdotenv-3.3+-blue?style=flat-square)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/code-distortion/fluent-dotenv/run-tests?label=tests&style=flat-square)](https://github.com/code-distortion/fluent-dotenv/actions)
[![Buy us a tree](https://img.shields.io/badge/treeware-%F0%9F%8C%B3-lightgreen?style=flat-square)](https://offset.earth/treeware?gift-trees)
[![Contributor Covenant](https://img.shields.io/badge/contributor%20covenant-v2.0%20adopted-ff69b4.svg?style=flat-square)](CODE_OF_CONDUCT.md)

***code-distortion/fluent-dotenv*** is a wrapper with a fluent interface for new and old versions of [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv).

## Introduction

.env files are an important tool, allowing for customisation of projects between environments.

First released in 2013, [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) by Vance Lucas and Graham Campbell is the most used PHP solution for loading values from .env files. Please have a look at their page for a more detailed description of what .env files are and why they're used.

Sometimes when you're building a package you need it to work with multiple versions of vlucas/phpdotenv so your own package can provide coverage. This was the original motivation behind this package.

***NOTE:*** Over time, vlucas/phpdotenv has improved the way it reads values from .env files (eg. multi-line variables). The underlying version you have will determine how values in .env files are read.

## Overview

This package provides a new fluent interface for vlucas/phpdotenv features, including the ability to:

- Specify values to explicitly *pick* or *ignore*,
- Specify values that are *required*,
- Perform validation - make sure values are *not empty*, are *integers*, *booleans*, are *limited to a specific set of values*, match a *regex* or validate via a *callback*,
- Populate *$_GET* and *$_SERVER* values if you like, choosing whether to override values that already exist or not.

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

// get all the values
$allValues = $fDotEnv->all();

// get a single value
$host = $fDotEnv->get('HOST');

// get several values (will be returned as an associative array)
$dbCredentials = $fDotEnv->get(['HOST', 'PORT', 'USERNAME', 'PASSWORD']);
```

### Filtering

If you only want to load specific keys you can specify them, others from your .env file will be ignored:

``` php
$fDotEnv = FluentDotEnv::new()
    ->pick('MY_VAL1')              // add a key to pick
    ->pick('MY_VAL2')              // values can be passed individually
    ->pick(['MY_VAL3', 'MY_VAL4']) // or multiple as an array
    ->load('path/to/.env');
```

Likewise particular keys can be ignored:

``` php
$fDotEnv = FluentDotEnv::new()
    ->ignore('MY_VAL1')              // add a key to ignore
    ->ignore('MY_VAL2')              // values can be passed individually
    ->ignore(['MY_VAL3', 'MY_VAL4']) // or multiple as an array
    ->load('path/to/.env');
```

### Validation

Validation can be applied to the values in an .env file. A `CodeDistortion\FluentDotEnv\Exceptions\ValidationException` exception will be thrown when a value fails validation:

``` php
$fDotEnv = FluentDotEnv::new()

    // make sure these keys exist
    ->required('MY_VAL')
    ->required(['MY_VAL1', 'MY_VAL2'])

    // when these keys exist, make sure they aren't empty
    ->notEmpty('MY_VAL')
    ->notEmpty(['MY_VAL1', 'MY_VAL2'])

    // when these keys exist, make sure they are integer strings
    ->integer('MY_VAL')
    ->integer(['MY_VAL1', 'MY_VAL2'])

    // when these keys exist, make sure they are boolean strings
    // ie. "true", "false", "On", "Off", "Yes", "No", "1" and "0"
    ->boolean('MY_VAL')
    ->boolean(['MY_VAL1', 'MY_VAL2'])

    // when a key exists, make sure its value is in a predefined list
    ->allowedValues('MY_VAL', ['value-1', 'value-2'])
    // allow predefined values for a multiple keys
    ->allowedValues(['MY_VAL1', 'MY_VAL2'], ['value-1', 'value-2'])
    // different predefined values can be specified for different keys in one go
    ->allowedValues([
        'MY_VAL1' => ['value-1', 'value-2'],
        'MY_VAL2' => ['value-a', 'value-b'],
    ])

    // when a key exists, make sure its value matches a regular expression
    ->regex('MY_VAL', '/^[0-9]+\.[0-9]{2}$/') 
    // the same regex can be applied to multiple keys
    ->regex(['MY_VAL1', 'MY_VAL2'], '/^[0-9]+\.[0-9]{2}$/')
    // different regexes can be applied to different keys in one go
    ->regex([
        'MY_VAL1' => '/^[0-9]+\.[0-9]{2}$/',
        'MY_VAL2' => '/^[a-z]+$/'
    ])

    // when a key exists, validate it's value via a callback
    ->callback('MY_VAL', $callback)
    // the same callback can be applied to multiple keys
    ->callback(['MY_VAL1', 'MY_VAL2'], $callback)
    // different callbacks can be applied to different keys in one go
    ->callback([
        'MY_VAL1' => $callback1,
        'MY_VAL2' => $callback2,
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
$boolean = $fDotEnv->castBoolean('MY_VAL');
$booleans = $fDotEnv->castBoolean(['MY_VAL1', 'MY_VAL2']);

// cast to an integer, including negative numbers
$integer = $fDotEnv->castInteger('MY_VAL');
$integers = $fDotEnv->castInteger(['MY_VAL1', 'MY_VAL2']);
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

The methods above can be called *before* or *after* loading values from .env files.

``` php
$fDotEnv = FluentDotEnv::new()
    ->load('path/to/.env') // loaded at the beginning
    ->pick(['MY_VAL1', 'MY_VAL2'])
    ->integer('MY_VAL1')
    ->boolean('MY_VAL2')
    ->populateEnv();

$fDotEnv = FluentDotEnv::new()
    ->pick(['MY_VAL1', 'MY_VAL2'])
    ->integer('MY_VAL1')
    ->boolean('MY_VAL2')
    ->populateEnv()
    ->load('path/to/.env'); // loaded at the end
```

***NOTE:*** When you call `populateEnv()` or `populateServer()` *after* `load()` has been called, the $_ENV and $_SERVER arrays respectively will be updated straight away.

### Other dotenv reader packages

[symfony/dotenv](https://github.com/symfony/dotenv) was first released in 2017 and is another commonly used dotenv reader.

You can use symfony/dotenv to read .env files by calling `useSymfonyDotEnv()` before calling `load()`. eg.

``` php
$fDotEnv = FluentDotEnv::new()
    ->useSymfonyDotEnv()
    ->load('path/to/.env')
```

***NOTE:*** symfony/dotenv must be included in your project as a dependency for this to work.

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

You're free to use this package, but if it makes it to your production environment please plant or buy a tree for the world.

It's now common knowledge that one of the best tools to tackle the climate crisis and keep our temperatures from rising above 1.5C is to <a href="https://www.bbc.co.uk/news/science-environment-48870920">plant trees</a>. If you support this package and contribute to the Treeware forest you'll be creating employment for local families and restoring wildlife habitats.

You can buy trees here [offset.earth/treeware](https://offset.earth/treeware?gift-trees)

Read more about Treeware at [treeware.earth](http://treeware.earth)

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Code of conduct

Please see [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

### Security

If you discover any security related issues, please email tim@code-distortion.net instead of using the issue tracker.

## Credits

- [Tim Chandler](https://github.com/code-distortion)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
