<?php declare(strict_types=1);
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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */
namespace OAT\Library\HealthCheck\Tests\Unit;

use Exception;
use OAT\Library\HealthCheck\Checker\CheckerInterface;
use OAT\Library\HealthCheck\HealthChecker;
use OAT\Library\HealthCheck\Result\CheckerResult;
use PHPUnit\Framework\TestCase;

class HealthCheckerTest extends TestCase
{
    public function testItCanBeConstructedWithCheckers(): void
    {
        $subject = new HealthChecker(
            [
                $this->buildChecker('successChecker', function () {
                    return new CheckerResult(true, 'success message');
                }),
                $this->buildChecker('failureChecker', function () {
                    throw new Exception('failure message');
                })
            ]
        );

        $results = $subject->performChecks();

        $this->assertCount(2, $results);
    }

    public function testItPerformChecksWithSingleSuccessfulChecker(): void
    {
        $subject = new HealthChecker();

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
    }

    public function testItPerformChecksWithSingleExpectedFailingChecker(): void
    {
        $subject = new HealthChecker();

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
    }

    public function testItPerformChecksWithSingleUnexpectedFailingChecker(): void
    {
        $subject = new HealthChecker();

        $subject->registerChecker(
            $this->buildChecker('failureChecker', function () {
                throw new Exception('failure message');
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
    }

    public function testItPerformChecksWithSeveralCheckers(): void
    {
        $subject = new HealthChecker();

        $subject
            ->registerChecker(
                $this->buildChecker('successChecker', function () {
                    return new CheckerResult(true, 'success message');
                })
            )
            ->registerChecker(
                $this->buildChecker('failureChecker', function () {
                    throw new Exception('failure message');
                })
            );

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
                        'message' => 'failure message'
                    ]
                ]
            ],
            $results->jsonSerialize()
        );
    }

    private function buildChecker(string $identifier, callable $checkerLogic): CheckerInterface
    {
        return new class ($identifier, $checkerLogic) implements CheckerInterface
        {
            /** @var string */
            private $identifier;

            /** @var callable */
            private $checkerLogic;

            public function __construct(string $identifier, callable $checkerLogic)
            {
                $this->identifier = $identifier;
                $this->checkerLogic = $checkerLogic;
            }

            public function getIdentifier(): string
            {
                return $this->identifier;
            }

            public function check(): CheckerResult
            {
                return call_user_func($this->checkerLogic);
            }
        };
    }
}
