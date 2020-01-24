# Health Check Library

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

On the `HealthChecker` class `performChecks()` method execution, a [CheckerResultCollection](src/Result/CheckerResultCollection.php) instance is returned, to make available global checkers success and detailed messages for each of them.

You need first to create a [CheckerInterface](src/Checker/CheckerInterface.php) as follow:

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

And then add the created checker to the [HealthChecker](src/HealthChecker.php), and perform checks as follow:

```php
<?php declare(strict_types=1);

use OAT\Library\HealthCheck\HealthChecker;

$healthChecker = new HealthChecker();

$results = $healthChecker
    ->addChecker(new MySuccessChecker())
    ->addChecker(new MyFailureChecker())
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