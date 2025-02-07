# Health Check Library

[![Latest Version](https://img.shields.io/github/tag/oat-sa/lib-health-check.svg?style=flat&label=release)](https://github.com/oat-sa/lib-health-check/tags)
[![License GPL2](http://img.shields.io/badge/licence-GPL%202.0-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://travis-ci.org/oat-sa/lib-health-check.svg?branch=master)](https://travis-ci.org/oat-sa/lib-health-check)
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

For example, you need first to create [CheckerInterface](src/Checker/CheckerInterface.php) implementations as follows:

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

**Notes**:
- you can provide to the `HealthChecker` (as 2nd constructor parameter) a [LoggerInterface](https://www.php-fig.org/psr/psr-3/#3-psrlogloggerinterface) instance to customise its logging behaviour.
- by default, the `NullLogger` will be used.
- it is recommended to catch only known exceptions in order to form an appropriate result message. The unknown exceptions and errors should be bubbled up to the `HealthCheker` level.

## Tests

To run tests:
```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.
