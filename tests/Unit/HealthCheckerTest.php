<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\HealthCheck\Tests\Unit;

use Exception;
use OAT\Library\HealthCheck\Checker\CheckerInterface;
use OAT\Library\HealthCheck\HealthChecker;
use OAT\Library\HealthCheck\Result\CheckerResult;
use PHPUnit\Framework\TestCase;
use ColinODell\PsrTestLogger\TestLogger;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;

class HealthCheckerTest extends TestCase
{
    public function testItCanBeConstructedWithPreRegisteredCheckers(): void
    {
        $logger = new TestLogger();

        $subject = new HealthChecker(
            [
                $this->buildChecker('successChecker', function () {
                    return new CheckerResult(true, 'success message');
                }),
                $this->buildChecker('failureChecker', function () {
                    return new CheckerResult(false, 'failure message');
                })
            ],
            $logger
        );

        $results = $subject->performChecks();

        $this->assertCount(2, $results);
        $this->assertTrue($logger->hasInfo('[health-check] checker successChecker success: success message'));
        $this->assertTrue($logger->hasError('[health-check] checker failureChecker failure: failure message'));
    }

    public function testItCanBeConstructedWithTwiceTheSameCheckerUnderDifferentIdentifier(): void
    {
        $logger = new TestLogger();

        $checker = $this->buildChecker('checker', function () {
            return new CheckerResult(true, 'success message');
        });

        $subject = new HealthChecker(
            [
                'checker1' => $checker,
                'checker2' => $checker,
            ],
            $logger
        );

        $results = $subject->performChecks();

        $this->assertCount(2, $results);

        $this->assertTrue($logger->hasInfo('[health-check] checker checker1 success: success message'));
        $this->assertTrue($logger->hasInfo('[health-check] checker checker2 success: success message'));
    }

    public function testItCanRegisterTwiceTheSameCheckerUnderDifferentIdentifier(): void
    {
        $logger = new TestLogger();

        $checker = $this->buildChecker('checker', function () {
            return new CheckerResult(true, 'success message');
        });

        $subject = new HealthChecker([], $logger);

        $subject
            ->registerChecker($checker, 'checker1')
            ->registerChecker($checker, 'checker2');

        $results = $subject->performChecks();

        $this->assertCount(2, $results);

        $this->assertTrue($logger->hasInfo('[health-check] checker checker1 success: success message'));
        $this->assertTrue($logger->hasInfo('[health-check] checker checker2 success: success message'));
    }

    public function testItPerformChecksWithNoCheckersRegistered(): void
    {
        $logger = new TestLogger();

        $subject = new HealthChecker([], $logger);

        $results = $subject->performChecks();

        $this->assertCount(0, $results);
        $this->assertFalse($results->hasErrors());
        $this->assertEquals(
            [
                'success' => true,
                'checkers' => []
            ],
            $results->jsonSerialize()
        );
    }

    public function testItPerformChecksWithSingleSuccessfulChecker(): void
    {
        $logger = new TestLogger();

        $subject = new HealthChecker([], $logger);

        $subject->registerChecker(
            $this->buildChecker('successChecker', function () {
                return new CheckerResult(true, 'success message');
            })
        );

        $results = $subject->performChecks();

        $this->assertCount(1, $results);
        $this->assertFalse($results->hasErrors());
        $this->assertEquals(
            [
                'success' => true,
                'checkers' => [
                    'successChecker' => [
                        'success' => true,
                        'message' => 'success message'
                    ]
                ]
            ],
            $results->jsonSerialize()
        );

        $this->assertTrue($logger->hasInfo('[health-check] checker successChecker success: success message'));
    }

    public function testItPerformChecksWithSingleExpectedFailingChecker(): void
    {
        $logger = new TestLogger();

        $subject = new HealthChecker([], $logger);

        $subject->registerChecker(
            $this->buildChecker('failureChecker', function () {
                return new CheckerResult(false, 'failure message');
            })
        );

        $results = $subject->performChecks();

        $this->assertCount(1, $results);
        $this->assertTrue($results->hasErrors());
        $this->assertEquals(
            [
                'success' => false,
                'checkers' => [
                    'failureChecker' => [
                        'success' => false,
                        'message' => 'failure message'
                    ]
                ]
            ],
            $results->jsonSerialize()
        );

        $this->assertTrue($logger->hasError('[health-check] checker failureChecker failure: failure message'));
    }

    public function testItPerformChecksWithSingleUnexpectedFailingChecker(): void
    {
        $logger = new TestLogger();

        $failingChecker = $this->buildChecker(
            'failureChecker',
            function () {
                throw new Exception('exception message');
            }
        );

        $subject = new HealthChecker([], $logger);
        $subject->registerChecker($failingChecker);

        $results = $subject->performChecks();

        $this->assertCount(1, $results);
        $this->assertTrue($results->hasErrors());
        $this->assertEquals(
            [
                'success' => false,
                'checkers' => [
                    'failureChecker' => [
                        'success' => false,
                        'message' => 'exception message'
                    ]
                ]
            ],
            $results->jsonSerialize()
        );

        $this->assertTrue($logger->hasError(
            $this->createRecord(
                '[health-check] checker failureChecker error: exception message',
                $failingChecker->getExceptionContext()
            )
        ));
    }

    public function testItPerformChecksWithSeveralCheckers(): void
    {
        $logger = new TestLogger();

        $failingChecker = $this->buildChecker(
            'failureChecker',
            function () {
                throw new Exception('exception message');
            }
        );

        $subject = new HealthChecker([], $logger);

        $subject
            ->registerChecker(
                $this->buildChecker('successChecker', function () {
                    return new CheckerResult(true, 'success message');
                })
            )
            ->registerChecker($failingChecker);

        $results = $subject->performChecks();

        $this->assertCount(2, $results);
        $this->assertTrue($results->hasErrors());
        $this->assertEquals(
            [
                'success' => false,
                'checkers' => [
                    'successChecker' => [
                        'success' => true,
                        'message' => 'success message'
                    ],
                    'failureChecker' => [
                        'success' => false,
                        'message' => 'exception message'
                    ]
                ]
            ],
            $results->jsonSerialize()
        );

        $this->assertTrue($logger->hasInfo('[health-check] checker successChecker success: success message'));
        $this->assertTrue($logger->hasError(
            $this->createRecord(
                '[health-check] checker failureChecker error: exception message',
                $failingChecker->getExceptionContext()
            )
        ));
    }

    private function buildChecker(string $identifier, callable $checkerLogic): CheckerInterface
    {
        return new class ($identifier, $checkerLogic) implements CheckerInterface
        {
            /** @var string */
            private $identifier;

            /** @var callable */
            private $checkerLogic;

            /** @var ?Throwable */
            private $exception;

            public function __construct(string $identifier, callable $checkerLogic)
            {
                $this->identifier = $identifier;
                $this->checkerLogic = $checkerLogic;
            }

            public function getIdentifier(): string
            {
                return $this->identifier;
            }

            public function getExceptionContext(): array
            {
                $flattenedException = FlattenException::createFromThrowable($this->exception);

                return [
                    'class' => $flattenedException->getClass(),
                    'file'  => $flattenedException->getFile(),
                    'line'  => $flattenedException->getLine(),
                    'trace' => $flattenedException->getTrace(),
                ];
            }

            public function check(): CheckerResult
            {
                try {
                    return call_user_func($this->checkerLogic);
                } catch (Throwable $exception) {
                    $this->exception = $exception;

                    throw $this->exception;
                }
            }
        };
    }

    private function createRecord(string $message, array $context = []): array
    {
        return compact('message', 'context');
    }
}
