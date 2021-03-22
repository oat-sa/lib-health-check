# Health Check Library

[![Latest Version](https://img.shields.io/github/tag/oat-sa/lib-health-check.svg?style=flat&label=release)](https://github.com/oat-sa/lib-health-check/tags)
[![License GPL2](http://img.shields.io/badge/licence-GPL%202.0-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://travis-ci.org/oat-sa/lib-health-check.svg?branch=master)](https://travis-ci.org/oat-sa/lib-health-check)
[![Coverage Status](https://coveralls.io/repos/github/oat-sa/lib-health-check/badge.svg?branch=master)](https://coveralls.io/github/oat-sa/lib-health-check?branch=master)
[![Packagist Downloads](http://img.shields.io/packagist/dt/oat-sa/lib-health-check.svg)](https://packagist.org/packages/oat-sa/lib-health-check)

> Health checks PHP library.

## Table of contents
- [Installation](#installation)
- [Usage](#usage)
- [Tests](#tests)

## Installation

```console
$ composer require oat-sa/lib-health-check
```

## Usage

This library provides a [HealthChecker](src/HealthChecker.php) object in charge to aggregate and execute implementations of the [CheckerInterface](src/Checker/CheckerInterface.php).

On the `HealthChecker` class `performChecks()` method execution, a [CheckerResultCollection](src/Result/CheckerResultCollection.php) instance is returned, aggregating all checkers results information.

By example, you need first to create [CheckerInterface](src/Checker/CheckerInterface.php) implementations as follow:

```php
<?php declare(strict_types=1);

use OAT\Library\HealthCheck\Checker\CheckerInterface;
use OAT\Library\HealthCheck\Result\CheckerResult;

class MySuccessChecker implements CheckerInterface
{
    public function getIdentifier() : string
    {
        return 'MySuccessChecker';
    }
    
    public function check() : CheckerResult
    {
        return new CheckerResult(true, 'my success message');
    }
}

class MyFailureChecker implements CheckerInterface
{
    public function getIdentifier() : string
    {
        return 'MyFailureChecker';
    }
    
    public function check() : CheckerResult
    {
        return new CheckerResult(false, 'my failure message');
    }
}
```

Then register the checkers into the [HealthChecker](src/HealthChecker.php), and perform checks as following:

```php
<?php declare(strict_types=1);

use OAT\Library\HealthCheck\HealthChecker;

$healthChecker = new HealthChecker();

$results = $healthChecker
    ->registerChecker(new MySuccessChecker())
    ->registerChecker(new MyFailureChecker())
    ->performChecks();

$results->hasErrors(); // true

foreach ($results as $result) {
    echo $result->getMessage();
}
```

## Tests

To run tests:
```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.